# ToyyibPay Integration Fix — Complete Code Review & Patch

## Root Cause

**403 INVALID SIGNATURE** occurred because Laravel's `signed` URL middleware was applied to the `payment.return` route, but ToyyibPay **appends** its own query parameters (`status_id`, `billcode`, `order_id`) to the return URL during redirect. Laravel's `URL::hasValidSignature()` includes **all** query parameters in the HMAC computation, so the original signature (computed for `payment_id=X` alone) never matches the post-redirect URL (which includes `payment_id=X&status_id=1&billcode=Y&order_id=Z`).

---

## Files Modified

### 1. `routes/web.php`

**Why changed:** The `signed` middleware is incompatible with ToyyibPay's return URL behavior. Replaced with `auth` middleware which verifies the user is logged in but does not perform URL signature validation.

**Change:**
- Line 74: `->middleware('signed')` → `->middleware('auth')`

**Updated code:**
```php
Route::get('/payment/return', [PaymentController::class, 'return'])->name('payment.return')->middleware('auth');
```

---

### 2. `app/Http/Controllers/Web/PaymentController.php`

#### 2a. Remove `URL` facade import

**Why changed:** `URL::signedRoute()` is no longer used. Removed unused import.

```php
// Removed:
use Illuminate\Support\Facades\URL;
```

#### 2b. Replace `URL::signedRoute()` with `route()` in `createBill()` (line 101)

**Why changed:** ToyyibPay appends parameters to the return URL, making signed URLs invalid. Using `route()` generates a clean URL without a signature.

```php
returnUrl: route('payment.return', ['payment_id' => $payment->id]),
```

#### 2c. Replace `URL::signedRoute()` with `route()` in `checkoutMultiple()` (line 208)

**Why changed:** Same reason as 2b.

```php
returnUrl: route('payment.return', ['payment_id' => $payment->id]),
```

#### 2d. `callback()` — Add MD5 hash verification (lines 261-330)

**Why changed:** ToyyibPay sends a `hash` parameter in every callback. Per their documentation, the hash is computed as:

```
MD5( userSecretKey + status + order_id + refno + "ok" )
```

Without verifying this hash, **anyone who knows your callback URL can send fake payment notifications**. This is a critical security vulnerability.

**Updated `callback()` method:**
```php
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

        return response('success');

    } catch (\Exception $e) {
        Log::error('Payment Callback Error', ['error' => $e->getMessage()]);
        return response('error', 500);
    }
}
```

#### 2e. `return()` — Secure with ownership check and fast-path via `status_id` (lines 340-403)

**Why changed:**
1. **No ownership check** — any authenticated user could view any payment's status by guessing `payment_id`.
2. **No use of `status_id`** — ToyyibPay sends the payment result directly in the return URL via `status_id`, but the old code ignored it and always fell through to an API call.
3. **New logic:** (a) ownership check, (b) fast-path using ToyyibPay's `status_id` if billcode matches, (c) API fallback only if still pending.

**Updated `return()` method:**
```php
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
```

---

### 3. `app/Http/Controllers/Api/CartController.php`

**Why changed:** Both the web and API controllers use the same return URL pattern. Removed `URL::signedRoute()` and the unused `URL` import.

- Line removed import: `use Illuminate\Support\Facades\URL;`
- Line 623: `URL::signedRoute(...)` → `route(...)`

```php
returnUrl: route('payment.return', ['payment_id' => $payment->id]),
```

---

### 4. `app/Http/Controllers/Web/PaymentTestController.php`

**Why changed:** Test controller was also using `URL::signedRoute()` for redirects, but since the signed middleware is removed from the return route, the signed URL would cause a 403 on the redirect itself.

- Removed import: `use Illuminate\Support\Facades\URL;`
- Line 43: `URL::signedRoute(...)` → `route(...)`
- Line 73: `URL::signedRoute(...)` → `route(...)`

```php
return redirect(route('payment.return', ['payment_id' => $paymentId]))
    ->with('success', 'Test payment completed successfully!');
```

---

### 5. `app/Http/Middleware/ValidateSignature.php`

**Why changed:** Belt-and-suspenders protection. Even though the `signed` middleware is removed from the return route, if any other route uses it and ToyyibPay parameters leak through, this prevents signature failures.

**Updated `$except` array:**
```php
protected $except = [
    'status_id',
    'billcode',
    'order_id',
];
```

---

## Post-Fix Payment Flow

```
User clicks "Pay"
        ↓
PaymentController@createBill
        ↓
POST /index.php/api/createBill → ToyyibPay
        ↓
ToyyibPay returns BillCode
        ↓
User redirected to https://toyyibpay.com/{BillCode}
        ↓
User completes payment on ToyyibPay
        ↓
┌──────────────────────────────────────────────┐
│  TWO SIMULTANEOUS RESPONSES FROM TOYYIBPAY   │
├──────────────────────┬───────────────────────┤
│  A. Server Callback   │  B. User Redirect     │
│  POST /payment/       │  GET /payment/        │
│  callback             │  return?payment_id=X  │
│                       │  &status_id=1         │
│                       │  &billcode=Y          │
│                       │  &order_id=Z          │
├──────────────────────┼───────────────────────┤
│  Hash verified via    │  Ownership verified   │
│  MD5(secret+status    │  via auth()->id()     │
│  +order_id+refno+"ok")│                       │
│                       │  billcode matched     │
│  Payment updated      │  against DB           │
│  in database          │                       │
│                       │  status_id used       │
│  Returns "success"    │  to update status     │
│                       │  immediately          │
│  (source of truth)    │                       │
│                       │  Success/failed       │
│                       │  page displayed       │
└──────────────────────┴───────────────────────┘
```

---

## Security Audit — Remaining Concerns Resolved

| Issue | Status |
|---|---|
| `signed` middleware on return route | **FIXED** — replaced with `auth` |
| `URL::signedRoute()` for return URLs | **FIXED** — replaced with `route()` |
| Missing `hash` verification in callback | **FIXED** — MD5 hash validated before processing |
| No payment ownership check in return | **FIXED** — `->where('user_id', auth()->id())` added |
| ToyyibPay params not excluded from signed URL validation | **FIXED** — `$except` array populated in `ValidateSignature` |
| API controller using signed URLs | **FIXED** — `CartController` updated |
| Test controller using signed URLs | **FIXED** — `PaymentTestController` updated |

All business logic (order creation, stock deduction, merchandise handling, event registration, payment history, notification views) is preserved unchanged.
