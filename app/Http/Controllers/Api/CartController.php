<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class CartController extends Controller
{
    private function formatCartItem(CartItem $cartItem): array
    {
        $price = (float) ($cartItem->unit_price ?? $cartItem->productVariant?->price ?? $cartItem->product?->price ?? 0);

        return [
            'id' => $cartItem->id,
            'user_id' => $cartItem->user_id,
            'product_id' => $cartItem->product_id,
            'product_variant_id' => $cartItem->product_variant_id,
            'quantity' => $cartItem->quantity,
            'price' => $price,
            'total_price' => $price * $cartItem->quantity,
            'variant_size' => $cartItem->variant_size,
            'variant_color' => $cartItem->variant_color,
            'created_at' => $cartItem->created_at,
            'updated_at' => $cartItem->updated_at,
            'product' => $cartItem->product,
            'product_variant' => $cartItem->productVariant,
        ];
    }

    private function resolveVariant(Product $product, ?int $variantId): ?ProductVariant
    {
        if (!$variantId) {
            return null;
        }

        return $product->variants()->whereKey($variantId)->first();
    }

    private function resolveProductPrice(Product $product, ?ProductVariant $variant = null): float
    {
        return (float) ($variant?->price ?? $product->price ?? 0);
    }

    private function resolveProductStock(Product $product, ?ProductVariant $variant = null): int
    {
        return (int) ($variant?->stock ?? $product->stock ?? 0);
    }

    private function unauthorizedResponse(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized - Please login first',
            'error_code' => 'UNAUTHENTICATED'
        ], 401);
    }

    private function notFoundResponse(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Cart item not found',
            'error_code' => 'CART_ITEM_NOT_FOUND'
        ], 404);
    }

    private function getToyyibPayConfig(): array
    {
        return [
            'api_url' => config('services.toyyibpay.api_url'),
            'category_code' => config('services.toyyibpay.category_code'),
            'secret_key' => config('services.toyyibpay.secret_key'),
        ];
    }

    private function generateExternalReference(): string
    {
        return 'ORD-' . strtoupper(Str::random(12)) . '-' . now()->timestamp;
    }

    private function resolveToyyibPayPayorDetails($user): array
    {
        $billEmail = trim((string) ($user->email ?? ''));
        $billPhone = trim((string) ($user->phone ?? ''));
        $billTo = trim((string) ($user->name ?? ''));

        if ($billTo === '' && $billEmail !== '') {
            $billTo = Str::of($billEmail)->before('@')->replace(['.', '_', '-'], ' ')->title()->toString();
        }

        if ($billTo === '') {
            $billTo = 'Customer';
        }

        if ($billPhone === '') {
            $billPhone = '60123456789';
        }

        return [
            'billTo' => $billTo,
            'billEmail' => $billEmail,
            'billPhone' => $billPhone,
        ];
    }

    private function validateToyyibPayPayorDetails(array $payorDetails): array
    {
        $missingFields = [];

        foreach (['billTo', 'billEmail', 'billPhone'] as $field) {
            if (trim((string) ($payorDetails[$field] ?? '')) === '') {
                $missingFields[] = $field;
            }
        }

        return $missingFields;
    }

    private function createToyyibPayBill(
        string $billName,
        string $billDescription,
        int $billAmount,
        string $billExternalReferenceNumber,
        string $returnUrl,
        string $callbackUrl,
        array $payorDetails
    ): array {
        try {
            $config = $this->getToyyibPayConfig();

            $data = [
                'userSecretKey' => $config['secret_key'],
                'categoryCode' => $config['category_code'],
                'billName' => substr($billName, 0, 30),
                'billDescription' => substr($billDescription, 0, 100),
                'billPriceSetting' => 1,
                'billPayorInfo' => 1,
                'billAmount' => $billAmount,
                'billReturnUrl' => $returnUrl,
                'billCallbackUrl' => $callbackUrl,
                'billExternalReferenceNo' => $billExternalReferenceNumber,
                'billPaymentChannel' => 2,
                'billTo' => $payorDetails['billTo'],
                'billEmail' => $payorDetails['billEmail'],
                'billPhone' => $payorDetails['billPhone'],
            ];

            Log::info('ToyyibPay API Request', [
                'url' => "{$config['api_url']}/index.php/api/createBill",
                'data' => array_merge($data, ['userSecretKey' => '***hidden***']),
            ]);

            $response = Http::asForm()->post(
                "{$config['api_url']}/index.php/api/createBill",
                $data
            );

            Log::info('ToyyibPay API Response', ['status' => $response->status(), 'body' => $response->body()]);

            $responseData = $response->json();

            if (!is_array($responseData) || empty($responseData)) {
                Log::error('ToyyibPay Bill Creation Failed - Empty Response', [
                    'request' => array_merge($data, ['userSecretKey' => '***hidden***']),
                    'response' => $response->body(),
                ]);

                return ['success' => false];
            }

            $bill = is_array($responseData[0] ?? null) ? $responseData[0] : $responseData;

            if (!isset($bill['BillCode']) || empty($bill['BillCode'])) {
                Log::error('ToyyibPay Bill Creation Failed - No BillCode', [
                    'request' => array_merge($data, ['userSecretKey' => '***hidden***']),
                    'response' => $responseData,
                ]);

                return ['success' => false];
            }

            $billCode = $bill['BillCode'];
            $baseUrl = rtrim($config['api_url'], '/');
            $billUrl = "{$baseUrl}/{$billCode}";

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
     * Add a product to cart.
     */
    public function add(Request $request)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            if (!$user) {
                return $this->unauthorizedResponse();
            }

            $validated = $request->validate([
                'product_id' => 'required|integer|exists:products,id',
                'quantity' => 'sometimes|integer|min:1',
                'product_variant_id' => 'nullable|integer|exists:product_variants,id',
            ]);

            $productId = (int) $validated['product_id'];
            $quantity = (int) ($validated['quantity'] ?? 1);
            $variantId = isset($validated['product_variant_id']) ? (int) $validated['product_variant_id'] : null;

            \Log::info('Add to cart request', [
                'user_id' => $user->id,
                'product_id' => $productId,
                'quantity' => $quantity,
                'product_variant_id' => $variantId,
            ]);

            $product = Product::with('variants')->find($productId);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                    'error_code' => 'PRODUCT_NOT_FOUND'
                ], 404);
            }

            $variant = $product->hasVariants() ? $this->resolveVariant($product, $variantId) : null;
            if ($product->hasVariants() && !$variant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select a valid product variant',
                    'error_code' => 'PRODUCT_VARIANT_NOT_FOUND'
                ], 422);
            }

            $unitPrice = $this->resolveProductPrice($product, $variant);
            $availableStock = $this->resolveProductStock($product, $variant);

            if ($quantity > $availableStock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Requested quantity exceeds available stock',
                    'error_code' => 'INSUFFICIENT_STOCK'
                ], 422);
            }

            $cartQuery = CartItem::where('user_id', $user->id)
                ->where('product_id', $productId);

            if ($variant) {
                $cartQuery->where('product_variant_id', $variant->id);
            } else {
                $cartQuery->whereNull('product_variant_id');
            }

            $cartItem = $cartQuery->first();

            if ($cartItem) {
                $newQuantity = $cartItem->quantity + $quantity;
                if ($newQuantity > $availableStock) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Requested quantity exceeds available stock',
                        'error_code' => 'INSUFFICIENT_STOCK'
                    ], 422);
                }

                $cartItem->quantity = $newQuantity;
                $cartItem->unit_price = $unitPrice;
                $cartItem->product_variant_id = $variant?->id;
                $cartItem->variant_size = $variant?->size;
                $cartItem->variant_color = $variant?->color;
                $cartItem->save();
                \Log::info('Updated existing cart item', [
                    'cart_item_id' => $cartItem->id,
                    'user_id' => $user->id,
                    'product_id' => $productId,
                    'quantity' => $cartItem->quantity,
                    'product_variant_id' => $variant?->id,
                ]);
            } else {
                $cartItem = CartItem::create([
                    'user_id' => $user->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'product_variant_id' => $variant?->id,
                    'unit_price' => $unitPrice,
                    'variant_size' => $variant?->size,
                    'variant_color' => $variant?->color,
                ]);
                \Log::info('Created cart item', [
                    'cart_item_id' => $cartItem->id,
                    'user_id' => $user->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'product_variant_id' => $variant?->id,
                ]);
            }

            $cartItem->load('product.media', 'productVariant');

            return response()->json([
                'success' => true,
                'message' => 'Added to cart successfully',
                'data' => $this->formatCartItem($cartItem)
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'error_code' => 'VALIDATION_FAILED'
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error adding to cart', [
                'user_id' => Auth::guard('sanctum')->user()?->id ?? 'null',
                'product_id' => $request->input('product_id'),
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to cart',
                'error_code' => 'ADD_CART_FAILED'
            ], 500);
        }
    }

    /**
     * Get current user's cart.
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            if (!$user) {
                return $this->unauthorizedResponse();
            }

            $items = CartItem::with('product.media', 'productVariant')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            $formattedItems = $items->map(fn (CartItem $item) => $this->formatCartItem($item))->values();
            $cartTotal = $formattedItems->sum('total_price');

            \Log::info('Get cart request', [
                'user_id' => $user->id,
                'count' => $items->count(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cart retrieved successfully',
                'count' => $items->count(),
                'cart_total' => $cartTotal,
                'data' => $formattedItems
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching cart', [
                'user_id' => Auth::guard('sanctum')->user()?->id ?? 'null',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch cart',
                'error_code' => 'FETCH_CART_FAILED'
            ], 500);
        }
    }

    /**
     * Update a cart item's quantity.
     */
    public function update(Request $request, int $cartItemId)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            if (!$user) {
                return $this->unauthorizedResponse();
            }

            $validated = $request->validate([
                'quantity' => 'required|integer|min:1',
            ]);

            $cartItem = CartItem::with('product.media', 'productVariant')
                ->where('id', $cartItemId)
                ->where('user_id', $user->id)
                ->first();

            if (!$cartItem) {
                return $this->notFoundResponse();
            }

            $availableStock = $this->resolveProductStock($cartItem->product, $cartItem->productVariant);
            if ((int) $validated['quantity'] > $availableStock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Requested quantity exceeds available stock',
                    'error_code' => 'INSUFFICIENT_STOCK'
                ], 422);
            }

            $cartItem->quantity = (int) $validated['quantity'];
            $cartItem->save();
            $cartItem->refresh()->load('product.media', 'productVariant');

            return response()->json([
                'success' => true,
                'message' => 'Cart item updated successfully',
                'data' => $this->formatCartItem($cartItem),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'error_code' => 'VALIDATION_FAILED'
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating cart item', [
                'user_id' => Auth::guard('sanctum')->user()?->id ?? 'null',
                'cart_item_id' => $cartItemId,
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update cart item',
                'error_code' => 'UPDATE_CART_FAILED'
            ], 500);
        }
    }

    /**
     * Remove an item from cart.
     */
    public function remove(int $cartItemId)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            if (!$user) {
                return $this->unauthorizedResponse();
            }

            $cartItem = CartItem::where('id', $cartItemId)
                ->where('user_id', $user->id)
                ->first();

            if (!$cartItem) {
                return $this->notFoundResponse();
            }

            $cartItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cart item removed successfully',
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error removing cart item', [
                'user_id' => Auth::guard('sanctum')->user()?->id ?? 'null',
                'cart_item_id' => $cartItemId,
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove cart item',
                'error_code' => 'REMOVE_CART_FAILED'
            ], 500);
        }
    }

    /**
     * Start checkout for the authenticated user's cart.
     */
    public function checkout(Request $request)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            if (!$user) {
                return $this->unauthorizedResponse();
            }

            $items = CartItem::with('product.media', 'productVariant')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your cart is empty',
                    'error_code' => 'EMPTY_CART',
                ], 400);
            }

            $totalAmount = 0;
            $productIds = [];
            $descriptions = [];

            foreach ($items as $item) {
                if (!$item->product) {
                    return response()->json([
                        'success' => false,
                        'message' => 'One or more cart items are no longer available',
                        'error_code' => 'INVALID_CART_ITEM',
                    ], 409);
                }

                if ($item->product->hasVariants() && !$item->productVariant) {
                    return response()->json([
                        'success' => false,
                        'message' => 'One or more cart items are missing a product variant',
                        'error_code' => 'INVALID_CART_ITEM',
                    ], 409);
                }

                $unitPrice = (float) ($item->unit_price ?? $item->productVariant?->price ?? $item->product?->price ?? 0);
                $availableStock = $this->resolveProductStock($item->product, $item->productVariant);

                if ((int) $item->quantity > $availableStock) {
                    return response()->json([
                        'success' => false,
                        'message' => 'One or more cart items are out of stock',
                        'error_code' => 'OUT_OF_STOCK',
                    ], 409);
                }

                $itemTotal = $unitPrice * (int) $item->quantity;
                $totalAmount += $itemTotal;
                $productIds[] = $item->product->id;
                $descriptions[] = $item->quantity . 'x ' . $item->product->name;
            }

            $billDescription = implode(', ', $descriptions);
            if (strlen($billDescription) > 100) {
                $billDescription = substr($billDescription, 0, 97) . '...';
            }

            $payorDetails = $this->resolveToyyibPayPayorDetails($user);
            $missingPayorFields = $this->validateToyyibPayPayorDetails($payorDetails);

            Log::info('Cart checkout payor details prepared', [
                'user_id' => $user->id,
                'billTo' => $payorDetails['billTo'],
                'billEmail' => $payorDetails['billEmail'],
                'billPhone' => $payorDetails['billPhone'],
                'missing_fields' => $missingPayorFields,
            ]);

            if (!empty($missingPayorFields)) {
                Log::warning('Cart checkout blocked due to missing ToyyibPay payor fields', [
                    'user_id' => $user->id,
                    'missing_fields' => $missingPayorFields,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Unable to start checkout because your payor details are incomplete.',
                    'error_code' => 'TOYYIBPAY_PAYOR_DETAILS_MISSING',
                    'missing_fields' => $missingPayorFields,
                ], 422);
            }

            $externalRef = $this->generateExternalReference();
            $payment = Payment::create([
                'user_id' => $user->id,
                'external_reference_no' => $externalRef,
                'payment_type' => 'merchandise',
                'related_id' => null,
                'amount' => $totalAmount,
                'status' => 'pending',
                'metadata' => json_encode([
                    'items' => $items->map(function (CartItem $item) {
                        return [
                            'id' => $item->product_id,
                            'product_variant_id' => $item->product_variant_id,
                            'quantity' => $item->quantity,
                            'price' => (float) ($item->unit_price ?? $item->productVariant?->price ?? $item->product?->price ?? 0),
                            'variant_size' => $item->variant_size,
                            'variant_color' => $item->variant_color,
                        ];
                    })->values(),
                    'product_ids' => $productIds,
                    'source' => 'mobile_cart_checkout',
                ]),
            ]);

            $billResponse = $this->createToyyibPayBill(
                billName: 'Cart Checkout',
                billDescription: $billDescription,
                billAmount: intval($totalAmount * 100),
                billExternalReferenceNumber: $externalRef,
                returnUrl: URL::signedRoute('payment.return', ['payment_id' => $payment->id]),
                callbackUrl: route('payment.callback'),
                payorDetails: $payorDetails,
            );

            if (!$billResponse['success']) {
                Log::error('ToyyibPay Bill Creation Failed', ['response' => $billResponse]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create payment. Please try again.',
                    'error_code' => 'CHECKOUT_FAILED',
                ], 500);
            }

            $payment->update([
                'bill_code' => $billResponse['bill_code'],
                'billpl_code' => $billResponse['billpl_code'],
                'bill_name' => $billResponse['bill_name'],
                'bill_description' => $billResponse['bill_description'],
                'bill_url' => $billResponse['bill_url'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Checkout started successfully',
                'data' => [
                    'payment_id' => $payment->id,
                    'payment_url' => $billResponse['bill_url'],
                    'bill_url' => $billResponse['bill_url'],
                    'bill_code' => $billResponse['bill_code'],
                    'amount' => $totalAmount,
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Cart Checkout Error', [
                'user_id' => Auth::guard('sanctum')->user()?->id ?? 'null',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while starting checkout',
                'error_code' => 'CHECKOUT_FAILED',
            ], 500);
        }
    }
}
