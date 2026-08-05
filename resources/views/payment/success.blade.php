<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/UITM_FAVICON.png') }}?v={{ filemtime(public_path('images/UITM_FAVICON.png')) }}">
    <title>Payment Successful | Campus Event Hub</title>
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
        
        <!-- Success Icon -->
        <div class="mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
        </div>

        <!-- Title -->
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Payment Successful!</h1>
        <p class="text-gray-600 mb-6">Your payment has been processed successfully.</p>

        <!-- Payment Details -->
        <div class="bg-gray-50 rounded-lg p-6 mb-6 text-left">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Amount</p>
                    <p class="text-lg font-bold text-gray-800">RM{{ number_format($payment->amount, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Status</p>
                    <p class="inline-block px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">Paid</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Reference</p>
                    <p class="text-sm font-mono text-gray-700">{{ $payment->external_reference_no }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Time</p>
                    <p class="text-sm text-gray-700">{{ $payment->transaction_time?->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Payment Type Info -->
        <div class="mb-6 text-left">
            @if($payment->payment_type === 'event_registration')
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                    <p class="text-sm text-blue-900">
                        <strong>Event Registration Confirmed</strong><br>
                        You are now registered for the event. Check your email for more details.
                    </p>
                </div>
            @elseif($payment->payment_type === 'merchandise')
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                    <p class="text-sm text-blue-900">
                        <strong>Order Confirmed</strong><br>
                        Your merchandise order has been placed. We'll send you tracking details soon.
                    </p>
                </div>
            @endif
        </div>

        <!-- Action Buttons -->
        <div class="space-y-3">
            @if($payment->payment_type === 'event_registration')
                <a href="{{ route('student.dashboard') }}" class="block w-full px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                    Back to Dashboard
                </a>
            @elseif($payment->payment_type === 'merchandise')
                <a href="{{ route('student.profile.orders') }}" class="block w-full px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                    View Order History
                </a>
            @endif
            
            <a href="{{ route('student.dashboard') }}" class="block w-full px-6 py-3 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition font-semibold">
                Go to Dashboard
            </a>
        </div>

        <!-- Help Text -->
        <p class="text-xs text-gray-500 mt-6">
            If you have any questions, please contact our support team.
        </p>
    </div>

    <script>
        // Clear the cart from localStorage after successful payment
        document.addEventListener('DOMContentLoaded', function() {
            localStorage.removeItem('cart');
            localStorage.removeItem('checkoutItems');
        });
    </script>
