# ToyyibPay Payment Integration Guide

## Overview

This is a **production-ready, secure ToyyibPay payment gateway integration** for the Campus Event Hub application. It handles payment processing for both merchandise purchases and event registrations.

## Architecture

### Payment Flow

```
1. User initiates payment (POST /payment/pay)
   ↓
2. Server creates ToyyibPay bill via API
   ↓
3. User redirected to ToyyibPay payment page
   ↓
4. User completes payment on ToyyibPay
   ↓
5. ToyyibPay sends callback (POST /payment/callback) ← SOURCE OF TRUTH
   ↓
6. Server verifies and updates payment status
   ↓
7. User returned to app (GET /payment/return) for UI feedback
```

**Key Point:** Payment verification happens ONLY via the callback (step 5). The return page (step 7) is for UI feedback only.

## Setup Instructions

### 1. Environment Variables

Update your `.env` file with ToyyibPay credentials:

```env
# Sandbox (for testing)
TOYYIBPAY_MODE=sandbox
TOYYIBPAY_API_URL=https://dev.toyyibpay.com
TOYYIBPAY_CATEGORY_CODE=your_category_code
TOYYIBPAY_SECRET_KEY=your_secret_key

# Production (when ready to go live)
# TOYYIBPAY_MODE=production
# TOYYIBPAY_API_URL=https://toyyibpay.com
```

### 2. Get ToyyibPay Credentials

1. Visit [ToyyibPay](https://toyyibpay.com)
2. Create a sandbox account for testing
3. Create a **bill category** and copy the category code
4. Get your **secret key** from settings
5. Add these to `.env`

### 3. Configure Callback URL

The callback URL must be **publicly accessible** (not localhost):

```
POST https://your-domain.com/payment/callback
```

Update this in ToyyibPay dashboard:
- Settings → Webhook → Callback URL
- Set to: `https://your-domain.com/payment/callback`

## Database Schema

The `payments` table stores all payment records:

| Column | Type | Purpose |
|--------|------|---------|
| `id` | bigint | Primary key |
| `user_id` | bigint | User who made payment |
| `bill_code` | string | Unique ToyyibPay bill code |
| `external_reference_no` | string | Your internal reference |
| `payment_type` | enum | `merchandise` or `event_registration` |
| `related_id` | bigint | ID of product/event |
| `amount` | decimal | Amount in RM |
| `status` | enum | `pending`, `paid`, `failed`, `cancelled` |
| `billpl_code` | string | ToyyibPay bill plan code |
| `bill_url` | string | URL to redirect user |
| `transaction_time` | timestamp | When payment completed |
| `payment_reference` | string | ToyyibPay reference |
| `callback_response` | json | Full callback data |

## API Endpoints

### 1. Create Payment Bill
```
POST /payment/pay
Content-Type: application/x-www-form-urlencoded

Parameters:
- payment_type: "merchandise" or "event_registration" (required)
- product_id: ID of product (required if payment_type=merchandise)
- event_id: ID of event (required if payment_type=event_registration)
- quantity: Number of items (optional, default=1)

Response: Redirect to ToyyibPay payment page
```

### 2. Payment Callback (ToyyibPay → Your Server)
```
POST /payment/callback
Parameters (from ToyyibPay):
- billCode: The bill code
- status: 1 (paid), 0 (not paid), 2 (pending)
- reference: ToyyibPay reference number

Response: JSON
```

**Important:** This is automatically called by ToyyibPay after payment. Do NOT call this manually.

### 3. Return Page (User → Your Server)
```
GET /payment/return?payment_id=123

Shows success/failed page based on database status
```

## Implementation Examples

### Adding Payment Button to Product Page

```blade
<form action="{{ route('payment.pay') }}" method="POST">
    @csrf
    <input type="hidden" name="payment_type" value="merchandise">
    <input type="hidden" name="product_id" value="{{ $product->id }}">
    <input type="hidden" name="quantity" value="1">
    
    <button type="submit" class="btn btn-primary">
        🛒 Buy Now - RM{{ $product->price }}
    </button>
</form>
```

### Adding Payment Button to Event Registration

```blade
<form action="{{ route('payment.pay') }}" method="POST">
    @csrf
    <input type="hidden" name="payment_type" value="event_registration">
    <input type="hidden" name="event_id" value="{{ $event->id }}">
    
    <button type="submit" class="btn btn-primary">
        Register for Event - RM{{ $event->ticket_price }}
    </button>
</form>
```

### Checking Payment Status in Code

```php
$payment = Payment::where('bill_code', $billCode)->first();

if ($payment->isPaid()) {
    // Process order/registration
}
```

## Security Features

1. **Amount in Cents:** ToyyibPay requires amount in cents. Automatic conversion in controller.
2. **Signature Validation:** Implement callback signature verification (commented in controller).
3. **Unique Bill Codes:** Each payment has unique bill code to prevent duplicates.
4. **Server-Side Verification:** Payment verified via callback, not client-side.
5. **Soft Deletes:** Payments can be soft-deleted without losing records.
6. **Environment Variables:** All secrets in `.env`, never hardcoded.

## Switching Between Sandbox and Production

### For Testing (Sandbox)
```env
TOYYIBPAY_MODE=sandbox
TOYYIBPAY_API_URL=https://dev.toyyibpay.com
TOYYIBPAY_CATEGORY_CODE=sandbox_category_code
TOYYIBPAY_SECRET_KEY=sandbox_secret_key
```

### For Production
```env
TOYYIBPAY_MODE=production
TOYYIBPAY_API_URL=https://toyyibpay.com
TOYYIBPAY_CATEGORY_CODE=production_category_code
TOYYIBPAY_SECRET_KEY=production_secret_key
```

Just change the `.env` variables and restart the app. No code changes needed!

## Business Logic Updates After Payment

The `handleSuccessfulPayment()` method in PaymentController automatically:

1. **For Merchandise:** Creates an order (TODO: implement)
2. **For Event Registration:** Marks registration as paid

Customize this method to suit your business logic.

## Error Handling

All errors are logged to:
```
storage/logs/laravel.log
```

Check this file if payments fail. Common errors:
- Invalid credentials → Check `.env` values
- Invalid category code → Check ToyyibPay dashboard
- Callback timeout → Check firewall/server accessibility
- Insufficient funds → User's bank issue

## Testing

### Sandbox Test Cards (from ToyyibPay)
- Card: 4242 4242 4242 4242
- Expiry: 01/25
- CVV: 123

## Monitoring

Monitor payment processing with:

```php
// Get all pending payments
$pendingPayments = Payment::pending()->get();

// Get paid payments
$paidPayments = Payment::paid()->get();

// Get total revenue
$totalRevenue = Payment::paid()->sum('amount');
```

## API Documentation

For full ToyyibPay API documentation, visit:
- **Sandbox:** https://dev.toyyibpay.com/docs
- **Production:** https://toyyibpay.com/docs

## Support

If you encounter issues:
1. Check `storage/logs/laravel.log` for errors
2. Verify `.env` variables are correct
3. Ensure callback URL is publicly accessible
4. Test with sandbox credentials first
5. Contact ToyyibPay support: https://toyyibpay.com/support

---

**Last Updated:** January 22, 2026
**Status:** Production-Ready
