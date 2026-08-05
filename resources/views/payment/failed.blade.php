<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/uitm_logo.png') }}?v={{ filemtime(public_path('images/uitm_logo.png')) }}">
    <title>Payment Failed | Campus Event Hub</title>
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
        
        <!-- Error Icon -->
        <div class="mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
        </div>

        <!-- Title -->
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Payment Failed</h1>
        <p class="text-gray-600 mb-6">Unfortunately, your payment could not be processed. Please try again.</p>

        <!-- Payment Details -->
        <div class="bg-gray-50 rounded-lg p-6 mb-6 text-left">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Amount</p>
                    <p class="text-lg font-bold text-gray-800">RM{{ number_format($payment->amount, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Status</p>
                    <p class="inline-block px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-semibold">Failed</p>
                </div>
                <div class="col-span-2">
                    <p class="text-sm text-gray-600">Reference</p>
                    <p class="text-sm font-mono text-gray-700">{{ $payment->external_reference_no }}</p>
                </div>
            </div>
        </div>

        <!-- Error Info -->
        <div class="mb-6">
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded text-left">
                <p class="text-sm text-red-900 font-semibold mb-2">Why did this happen?</p>
                <ul class="text-sm text-red-800 space-y-1 list-disc list-inside">
                    <li>Insufficient funds in your account</li>
                    <li>Card declined by your bank</li>
                    <li>Payment was cancelled</li>
                    <li>Payment gateway timeout</li>
                </ul>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-3">
            <button onclick="window.history.back()" class="w-full px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                Try Again
            </button>
            
            <a href="{{ route('student.dashboard') }}" class="block w-full px-6 py-3 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition font-semibold">
                Return to Dashboard
            </a>
        </div>

        <!-- Help Text -->
        <p class="text-xs text-gray-500 mt-6">
            If you continue to experience issues, please contact our support team.
        </p>
    </div>
</body>
</html>
