<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * PaymentController - Handles ToyyibPay payment integration
 * 
 * Payment Flow:
 * 1. User initiates payment (POST /payment/pay)
 * 2. Create ToyyibPay bill via API
 * 3. Redirect user to ToyyibPay payment page
 * 4. User completes payment on ToyyibPay
 * 5. ToyyibPay sends callback to our server (POST /payment/callback) - THIS IS THE SOURCE OF TRUTH
 * 6. We verify and update payment status
 * 7. User returns to app (GET /payment/return) - for UI feedback only, not for verification
 */
class PaymentController extends Controller
{
    /**
     * ToyyibPay API Configuration
     * Using environment variables for flexibility between sandbox and production
     */
    private function getToyyibPayConfig(): array
    {
        return [
            'api_url' => config('services.toyyibpay.api_url'),
            'category_code' => config('services.toyyibpay.category_code'),
            'secret_key' => config('services.toyyibpay.secret_key'),
        ];
    }

    /**
     * POST /payment/pay
     * 
     * Initiates payment by creating a ToyyibPay bill
     * Expects: product_id or event_id, quantity (optional)
     * Returns: Redirect to ToyyibPay payment page
     */
    public function createBill(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'payment_type' => 'required|in:merchandise,event_registration',
                'product_id' => 'required_if:payment_type,merchandise|exists:products,id',
                'product_variant_id' => 'nullable|exists:product_variants,id',
                'event_id' => 'required_if:payment_type,event_registration|exists:events,id',
                'quantity' => 'nullable|integer|min:1',
            ]);
            $user = auth()->user();
            if ($validated['payment_type'] === 'merchandise') {
                $product = Product::with('variants')->findOrFail($validated['product_id']);
                $quantity = $validated['quantity'] ?? 1;
                $variant = null;
                if ($product->hasVariants() && !empty($validated['product_variant_id'])) {
                    $variant = $product->variants()->whereKey($validated['product_variant_id'])->first();
                }
                if ($product->hasVariants() && !$variant) {
                    return back()->with('error', 'Please select a valid product variant.');
                }
                $unitPrice = (float) ($variant?->price ?? $product->price);
                $amount = $unitPrice * $quantity;
                $description = $variant
                    ? "{$quantity}x {$product->name} ({$variant->size} / {$variant->color})"
                    : "{$quantity}x {$product->name}";
                $relatedId = $product->id;
            } else {
                $event = Event::findOrFail($validated['event_id']);
                $amount = $event->ticket_price ?? 0;
                $description = "Event Registration: {$event->name}";
                $relatedId = $event->id;
            }
            $externalRef = $this->generateExternalReference();
            $payment = Payment::create([
                'user_id' => $user->id,
                'external_reference_no' => $externalRef,
                'payment_type' => $validated['payment_type'],
                'related_id' => $relatedId,
                'amount' => $amount,
                'status' => 'pending',
            ]);

            // Call ToyyibPay API to create bill
            $billResponse = $this->createToyyibPayBill(
                billName: $description,
                billDescription: "Order {$externalRef}",
                billAmount: intval($amount * 100), // Convert to cents
                billExternalReferenceNumber: $externalRef,
                returnUrl: route('payment.return', ['payment_id' => $payment->id]),
                callbackUrl: route('payment.callback')
            );

            if (!$billResponse['success']) {
                Log::error('ToyyibPay Bill Creation Failed', ['response' => $billResponse]);
                return back()->with('error', 'Failed to create payment. Please try again.');
            }

            // Store bill details in payment record
            $payment->update([
                'bill_code' => $billResponse['bill_code'],
                'billpl_code' => $billResponse['billpl_code'],
                'bill_name' => $billResponse['bill_name'],
                'bill_description' => $billResponse['bill_description'],
                'bill_url' => $billResponse['bill_url'],
            ]);

            Log::info('Payment Bill Created', [
                'payment_id' => $payment->id,
                'bill_code' => $billResponse['bill_code'],
                'amount' => $amount,
            ]);

            // Redirect user to ToyyibPay payment page
            return redirect($billResponse['bill_url']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Payment Creation Error', ['error' => $e->getMessage()]);
            return back()->with('error', 'An error occurred. Please try again.');
        }
    }

    /**
     * POST /payment/checkout-multiple
     * 
     * Handles checkout for multiple items from cart
     * Expects: items (JSON array with id, quantity, price)
     */
    public function checkoutMultiple(Request $request)
    {
        try {
            $validated = $request->validate([
                'items' => 'required|json',
            ]);

            $user = auth()->user();
            $items = json_decode($validated['items'], true);

            if (empty($items)) {
                return back()->with('error', 'No items selected for checkout.');
            }

            // Calculate total amount and build description
            $totalAmount = 0;
            $productIds = [];
            $descriptions = [];

            foreach ($items as $item) {
                $product = Product::findOrFail($item['id']);
                $variant = null;

                if (!empty($item['product_variant_id'])) {
                    $variant = ProductVariant::where('product_id', $product->id)->whereKey($item['product_variant_id'])->first();
                }

                if ($product->hasVariants() && !$variant) {
                    continue;
                }

                $unitPrice = (float) ($item['price'] ?? $variant?->price ?? $product->price);
                $itemTotal = $unitPrice * $item['quantity'];
                $totalAmount += $itemTotal;
                $productIds[] = $product->id;
                $descriptions[] = $variant
                    ? "{$item['quantity']}x {$product->name} ({$variant->size} / {$variant->color})"
                    : "{$item['quantity']}x {$product->name}";
            }

            $billDescription = implode(', ', $descriptions);
            if (strlen($billDescription) > 100) {
                $billDescription = substr($billDescription, 0, 97) . '...';
            }

            // Create payment record for multiple items
            $externalRef = $this->generateExternalReference();
            $payment = Payment::create([
                'user_id' => $user->id,
                'external_reference_no' => $externalRef,
                'payment_type' => 'merchandise',
                'related_id' => null, // Multiple items, no single related_id
                'amount' => $totalAmount,
                'status' => 'pending',
                'metadata' => json_encode([
                    'items' => $items,
                    'product_ids' => $productIds,
                ]),
            ]);

            // Call ToyyibPay API to create bill
            $billResponse = $this->createToyyibPayBill(
                billName: 'Multiple Items Purchase',
                billDescription: $billDescription,
                billAmount: intval($totalAmount * 100), // Convert to cents
                billExternalReferenceNumber: $externalRef,
                returnUrl: route('payment.return', ['payment_id' => $payment->id]),
                callbackUrl: route('payment.callback')
            );

            if (!$billResponse['success']) {
                Log::error('ToyyibPay Bill Creation Failed', ['response' => $billResponse]);
                return back()->with('error', 'Failed to create payment. Please try again.');
            }

            // Store bill details in payment record
            $payment->update([
                'bill_code' => $billResponse['bill_code'],
                'billpl_code' => $billResponse['billpl_code'],
                'bill_name' => $billResponse['bill_name'],
                'bill_description' => $billResponse['bill_description'],
                'bill_url' => $billResponse['bill_url'],
            ]);

            Log::info('Multiple Items Payment Bill Created', [
                'payment_id' => $payment->id,
                'bill_code' => $billResponse['bill_code'],
                'amount' => $totalAmount,
                'item_count' => count($items),
            ]);

            // Redirect user to ToyyibPay payment page
            return redirect($billResponse['bill_url']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Multiple Items Payment Creation Error', ['error' => $e->getMessage()]);
            return back()->with('error', 'An error occurred. Please try again.');
        }
    }

    /**
     * POST /payment/callback
     * 
     * Server-side callback from ToyyibPay
     * THIS IS THE SOURCE OF TRUTH for payment verification
     * ToyyibPay sends this after user completes/cancels payment
     * 
     * Expected parameters from ToyyibPay:
     * - billcode: The bill code we created
     * - status: 1 (success), 2 (pending), 3 (fail)
     * - refno: ToyyibPay's reference number
     * - order_id: Our external reference no (billExternalReferenceNo)
     * - hash: MD5 signature for verification
     * 
     * Hash verification formula (from ToyyibPay docs):
     * MD5( userSecretKey + status + order_id + refno + "ok" )
     */
    public function callback(Request $request)
    {
        try {
            Log::info('Payment Callback Received', $request->all());

            $billCode = $request->input('billcode');
            $status = $request->input('status');
            $refno = $request->input('refno');
            $orderId = $request->input('order_id');
            $receivedHash = $request->input('hash');

            // Verify callback authenticity using ToyyibPay's MD5 hash
            $expectedHash = md5(
                config('services.toyyibpay.secret_key')
                . $status
                . $orderId
                . $refno
                . 'ok'
            );

            if ($receivedHash !== $expectedHash) {
                Log::warning('ToyyibPay Callback Hash Mismatch', [
                    'expected' => $expectedHash,
                    'received' => $receivedHash,
                    'bill_code' => $billCode,
                    'status' => $status,
                    'refno' => $refno,
                    'order_id' => $orderId,
                ]);
                return response('Invalid hash', 403);
            }

            // Find payment by bill code
            $payment = Payment::where('bill_code', $billCode)->first();

            if (!$payment) {
                Log::warning('Payment Not Found for Callback', ['bill_code' => $billCode]);
                return response('Bill not found', 404);
            }

            // Update payment status based on ToyyibPay response
            // 1 = success, 2 = pending, 3 = fail
            if ($status == 1) {
                $payment->markAsPaid();
                $payment->update([
                    'transaction_time' => now(),
                    'payment_reference' => $refno,
                    'callback_response' => $request->all(),
                ]);
                $this->handleSuccessfulPayment($payment);
                Log::info('Payment Marked as Paid', ['payment_id' => $payment->id]);
            } elseif ($status == 3) {
                $payment->markAsFailed();
                $payment->update([
                    'callback_response' => $request->all(),
                ]);
                Log::info('Payment Marked as Failed', ['payment_id' => $payment->id]);
            } else {
                $payment->update(['status' => 'pending']);
                Log::info('Payment Status Pending', ['payment_id' => $payment->id]);
            }

            // Return success to ToyyibPay
            return response('success');

        } catch (\Exception $e) {
            Log::error('Payment Callback Error', ['error' => $e->getMessage()]);
            return response('error', 500);
        }
    }

    /**
     * GET /payment/return
     * 
     * User-facing return page after payment
     * ToyyibPay appends: status_id (1=success, 3=fail), billcode, order_id
     * Uses these parameters directly when available (fast path)
     * Falls back to API verification if still pending
     */
    public function return(Request $request)
    {
        try {
            $paymentId = $request->input('payment_id');

            // Verify payment belongs to the authenticated user
            $payment = Payment::where('id', $paymentId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            // If already processed by callback, show status immediately
            if ($payment->status !== 'pending') {
                $view = $payment->isPaid() ? 'payment.success' : 'payment.failed';
                return view($view, compact('payment'));
            }

            // ToyyibPay appends status_id, billcode, order_id to return URL
            $statusId = $request->input('status_id');
            $billcode = $request->input('billcode');

            // Fast path: use ToyyibPay's redirect parameters if billcode matches
            if ($statusId !== null && $billcode !== null && $billcode === $payment->bill_code) {
                if ($statusId == 1) {
                    $payment->update([
                        'status' => 'paid',
                        'transaction_time' => now(),
                        'callback_response' => array_merge($payment->callback_response ?? [], [
                            'return_url_status_id' => $statusId,
                            'return_url_billcode' => $billcode,
                            'return_url_order_id' => $request->input('order_id'),
                        ]),
                    ]);
                    $this->handleSuccessfulPayment($payment);
                    Log::info('Payment Confirmed via Return URL', ['payment_id' => $payment->id]);
                    return view('payment.success', compact('payment'));
                } elseif ($statusId == 3) {
                    $payment->update([
                        'status' => 'failed',
                        'callback_response' => array_merge($payment->callback_response ?? [], [
                            'return_url_status_id' => $statusId,
                            'return_url_billcode' => $billcode,
                        ]),
                    ]);
                    Log::info('Payment Failed via Return URL', ['payment_id' => $payment->id]);
                    return view('payment.failed', compact('payment'));
                }
            }

            // Fallback: verify via API (handles delayed callbacks, ngrok, etc.)
            if ($payment->bill_code) {
                $verified = $this->verifyPaymentStatusFromToyyibPay($payment);
                if ($verified && $payment->isPaid()) {
                    return view('payment.success', compact('payment'));
                }
            }

            // Payment is still pending or verification failed
            return view('payment.failed', compact('payment'));

        } catch (\Exception $e) {
            Log::error('Payment Return Error', ['error' => $e->getMessage()]);
            return back()->with('error', 'An error occurred');
        }
    }

    /**
     * Handle successful payment
     * Update related records (orders, registrations, etc.)
     */
    private function handleSuccessfulPayment(Payment $payment): void
    {
        if ($payment->payment_type === 'merchandise') {
            // For merchandise: Create order(s) from payment
            $this->createOrdersFromPayment($payment);
            $this->clearPurchasedItemsFromCart($payment);

        } elseif ($payment->payment_type === 'event_registration') {
            // For event registration: Mark registration as paid
            $registration = \App\Models\StudentEventRegistration::where('user_id', $payment->user_id)
                ->where('event_id', $payment->related_id)
                ->first();

            if ($registration) {
                $registration->update(['is_paid' => true]);
                Log::info('Event Registration Marked as Paid', ['registration_id' => $registration->id]);
            }
        }
    }

    /**
     * Remove purchased merchandise items from the user's cart after checkout completes.
     */
    private function clearPurchasedItemsFromCart(Payment $payment): void
    {
        if ($payment->metadata) {
            $metadata = json_decode($payment->metadata, true);
            if (isset($metadata['items']) && is_array($metadata['items'])) {
                $deletedCount = 0;

                foreach ($metadata['items'] as $item) {
                    if (!isset($item['id'])) {
                        continue;
                    }

                    $query = CartItem::where('user_id', $payment->user_id)
                        ->where('product_id', (int) $item['id']);

                    if (!empty($item['product_variant_id'])) {
                        $query->where('product_variant_id', (int) $item['product_variant_id']);
                    } else {
                        $query->whereNull('product_variant_id');
                    }

                    $deletedCount += $query->delete();
                }

                Log::info('Cleared purchased items from cart', [
                    'payment_id' => $payment->id,
                    'user_id' => $payment->user_id,
                    'deleted_count' => $deletedCount,
                ]);

                return;
            }
        }

        $productIds = [];

        if ($payment->related_id) {
            $productIds[] = (int) $payment->related_id;
        }

        $productIds = array_values(array_unique(array_filter($productIds)));

        if (empty($productIds)) {
            return;
        }

        $deletedCount = CartItem::where('user_id', $payment->user_id)
            ->whereIn('product_id', $productIds)
            ->delete();

        Log::info('Cleared purchased items from cart', [
            'payment_id' => $payment->id,
            'user_id' => $payment->user_id,
            'deleted_count' => $deletedCount,
        ]);
    }

    /**
     * Create orders from payment (handles both single and multiple items)
     */
    private function createOrdersFromPayment(Payment $payment): void
    {
        $user = $payment->user;

        Log::info('Creating Orders from Payment', [
            'payment_id' => $payment->id,
            'payment_type' => $payment->payment_type,
            'related_id' => $payment->related_id,
            'metadata' => $payment->metadata,
        ]);

        // Check if this is a multiple items payment
        if ($payment->metadata) {
            $metadata = json_decode($payment->metadata, true);
            if (isset($metadata['items']) && is_array($metadata['items'])) {
                // Multiple items checkout
                foreach ($metadata['items'] as $item) {
                    $product = Product::find($item['id']);
                    if ($product) {
                        $variant = null;
                        if (!empty($item['product_variant_id'])) {
                            $variant = ProductVariant::where('product_id', $product->id)->whereKey($item['product_variant_id'])->first();
                        }

                        $this->createOrder(
                            payment: $payment,
                            product: $product,
                            quantity: (int) $item['quantity'],
                            variant: $variant,
                            unitPrice: (float) ($item['price'] ?? $variant?->price ?? $product->price)
                        );
                    }
                }
                Log::info('Multiple Orders Created from Payment', [
                    'payment_id' => $payment->id,
                    'order_count' => count($metadata['items']),
                ]);
                return;
            }
        }

        // Single item checkout
        if ($payment->related_id) {
            $product = Product::find($payment->related_id);
            if ($product) {
                // Try to extract quantity from payment metadata or use 1
                $quantity = 1;
                    $variant = null;
                    $unitPrice = (float) $product->price;
                if ($payment->metadata) {
                    $metadata = json_decode($payment->metadata, true);
                    $quantity = $metadata['quantity'] ?? 1;
                        if (!empty($metadata['product_variant_id'])) {
                            $variant = ProductVariant::where('product_id', $product->id)->whereKey($metadata['product_variant_id'])->first();
                        }
                        $unitPrice = (float) ($metadata['price'] ?? $variant?->price ?? $product->price);
                }
                    $this->createOrder($payment, $product, $quantity, $variant, $unitPrice);
            } else {
                Log::warning('Product Not Found for Order Creation', [
                    'payment_id' => $payment->id,
                    'product_id' => $payment->related_id,
                ]);
            }
        } else {
            Log::warning('No Related ID or Metadata for Order Creation', [
                'payment_id' => $payment->id,
            ]);
        }
    }

    /**
     * Create a single order record
     */
    private function createOrder(Payment $payment, Product $product, int $quantity, ?ProductVariant $variant = null, ?float $unitPrice = null): void
    {
        try {
            DB::transaction(function () use ($payment, $product, $quantity, $variant, $unitPrice) {
                $resolvedUnitPrice = $unitPrice ?? (float) ($variant?->price ?? $product->price);

                \App\Models\Order::create([
                    'user_id' => $payment->user_id,
                    'order_id' => $payment->external_reference_no,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'customer_name' => $payment->user->name,
                    'quantity' => $quantity,
                    'unit_price' => $resolvedUnitPrice,
                    'variant_size' => $variant?->size,
                    'variant_color' => $variant?->color,
                    'total' => $quantity,
                    'total_price' => $resolvedUnitPrice * $quantity,
                    'payment_method' => 'toyyibpay',
                    'status' => 'completed',
                    'date' => now(),
                ]);

                if ($variant) {
                    $variant->decrement('stock', $quantity);
                    $product->forceFill(['stock' => (int) $product->variants()->sum('stock')])->save();
                } else {
                    $product->decrement('stock', $quantity);
                }
            });

            Log::info('Order Created from Payment', [
                'payment_id' => $payment->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'product_variant_id' => $variant?->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Order Creation Failed', [
                'payment_id' => $payment->id,
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create ToyyibPay Bill
     * Follows ToyyibPay API specification exactly
     * 
     * Returns:
     * [
     *   'success' => bool,
     *   'bill_code' => string,
     *   'bill_url' => string,
     * ]
     */
    private function createToyyibPayBill(
        string $billName,
        string $billDescription,
        int $billAmount, // In cents
        string $billExternalReferenceNumber,
        string $returnUrl,
        string $callbackUrl
    ): array {
        try {
            $config = $this->getToyyibPayConfig();

            // According to ToyyibPay API documentation, parameters should be:
            $data = [
                'userSecretKey' => $config['secret_key'],
                'categoryCode' => $config['category_code'],
                'billName' => substr($billName, 0, 30), // Max 30 alphanumeric chars
                'billDescription' => substr($billDescription, 0, 100), // Max 100 alphanumeric chars
                'billPriceSetting' => 1, // 1 = fixed price
                'billPayorInfo' => 1, // 1 = collect payor info
                'billAmount' => $billAmount, // Must be in cents
                'billReturnUrl' => $returnUrl,
                'billCallbackUrl' => $callbackUrl,
                'billExternalReferenceNo' => $billExternalReferenceNumber,
                'billPaymentChannel' => 2, // 2 = both FPX & Credit Card
                'billTo' => auth()->user()->name ?? 'Customer', // Required field
                'billEmail' => auth()->user()->email, // Required field - customer email
                'billPhone' => auth()->user()->phone ?? '60123456789', // Required field - customer phone (use default if not available)
            ];

            Log::info('ToyyibPay API Request', [
                'url' => "{$config['api_url']}/index.php/api/createBill",
                'data' => array_merge($data, ['userSecretKey' => '***hidden***']),
            ]);

            // Make API request to ToyyibPay
            $response = Http::asForm()->post(
                "{$config['api_url']}/index.php/api/createBill",
                $data
            );

            Log::info('ToyyibPay API Response', ['status' => $response->status(), 'body' => $response->body()]);

            $responseData = $response->json();

            // Check if we got a valid response
            if (!is_array($responseData) || empty($responseData)) {
                Log::error('ToyyibPay Bill Creation Failed - Empty Response', [
                    'request' => array_merge($data, ['userSecretKey' => '***hidden***']),
                    'response' => $response->body(),
                ]);
                return ['success' => false];
            }

            // ToyyibPay returns an array with a single object containing BillCode
            $bill = is_array($responseData[0] ?? null) ? $responseData[0] : $responseData;

            if (!isset($bill['BillCode']) || empty($bill['BillCode'])) {
                Log::error('ToyyibPay Bill Creation Failed - No BillCode', [
                    'request' => array_merge($data, ['userSecretKey' => '***hidden***']),
                    'response' => $responseData,
                ]);
                return ['success' => false];
            }

            $billCode = $bill['BillCode'];
            
            // Construct bill URL - format: {api_url}/{billCode}
            // For sandbox: https://dev.toyyibpay.com/gcbhict9
            // For production: https://toyyibpay.com/gcbhict9
            $baseUrl = rtrim($config['api_url'], '/');
            $billUrl = "{$baseUrl}/{$billCode}";

            Log::info('ToyyibPay Bill Created Successfully', [
                'bill_code' => $billCode,
                'bill_url' => $billUrl,
            ]);

            // Return bill details
            return [
                'success' => true,
                'bill_code' => $billCode,
                'billpl_code' => $billCode,
                'bill_name' => $billName,
                'bill_description' => $billDescription,
                'bill_url' => $billUrl,
            ];

        } catch (\Exception $e) {
            Log::error('ToyyibPay API Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['success' => false];
        }
    }

    /**
     * Generate unique external reference number
     * Used to track payments in our system
     */
    private function generateExternalReference(): string
    {
        return 'ORD-' . strtoupper(Str::random(12)) . '-' . now()->timestamp;
    }

    /**
     * Verify payment status directly from ToyyibPay API
     * Used when callback didn't arrive (e.g., ngrok limitations)
     * Updates payment status if verified
     * 
     * @return bool True if status was verified and updated, false otherwise
     */
    private function verifyPaymentStatusFromToyyibPay(Payment $payment): bool
    {
        try {
            if (!$payment->bill_code) {
                return false;
            }

            $config = $this->getToyyibPayConfig();

            // Call ToyyibPay API to check bill status
            $response = Http::asForm()->post(
                "{$config['api_url']}/index.php/api/getBillTransactionStatus",
                [
                    'userSecretKey' => $config['secret_key'],
                    'billCode' => $payment->bill_code,
                ]
            );

            Log::info('ToyyibPay Status Verification', [
                'bill_code' => $payment->bill_code,
                'status_code' => $response->status(),
                'response' => $response->body(),
            ]);

            $responseData = $response->json();

            // Parse response - ToyyibPay returns various statuses
            if (isset($responseData[0]['transactionStatus'])) {
                $transactionStatus = $responseData[0]['transactionStatus'];
                
                // Status codes from ToyyibPay:
                // 1 = Successful, 2 = Pending, 3 = Failed
                if ($transactionStatus == 1) {
                    $payment->markAsPaid();
                    $payment->update([
                        'transaction_time' => now(),
                        'payment_reference' => $responseData[0]['transactionRefNo'] ?? null,
                        'callback_response' => array_merge($payment->callback_response ?? [], [
                            'verified_at_return' => now()->toIso8601String(),
                            'verification_response' => $responseData,
                        ]),
                    ]);
                    $this->handleSuccessfulPayment($payment);
                    
                    Log::info('Payment Verified as Successful via API', [
                        'payment_id' => $payment->id,
                        'bill_code' => $payment->bill_code,
                    ]);
                    
                    return true;
                } elseif ($transactionStatus == 3) {
                    $payment->markAsFailed();
                    Log::info('Payment Verified as Failed via API', [
                        'payment_id' => $payment->id,
                        'bill_code' => $payment->bill_code,
                    ]);
                    return true;
                }
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Payment Status Verification Error', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
