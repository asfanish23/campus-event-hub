<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="{{ asset('images/UITM_FAVICON.png') }}?v={{ filemtime(public_path('images/UITM_FAVICON.png')) }}">
    <title>Forgot Password | Campus Event Hub</title>
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-purple-50 flex items-center justify-center">

<div class="w-full max-w-6xl grid grid-cols-1 lg:grid-cols-2 gap-8 p-6">

    {{-- LEFT --}}
    <div class="hidden lg:flex flex-col justify-center space-y-8">
        <div>
            <h1 class="text-5xl font-bold">Campus Event Hub</h1>
            <p class="text-xl text-gray-600">UiTM Event Management System</p>
        </div>

        <ul class="space-y-4">
            <li class="flex gap-3">
                <span class="text-purple-600">👤</span>
                <div>
                    <h3 class="font-semibold">Club Management</h3>
                    <p class="text-sm text-gray-600">Manage events & attendance</p>
                </div>
            </li>
            <li class="flex gap-3">
                <span class="text-purple-600">🛡️</span>
                <div>
                    <h3 class="font-semibold">HEP Administration</h3>
                    <p class="text-sm text-gray-600">Approve & monitor clubs</p>
                </div>
            </li>
        </ul>

        <p class="text-sm text-gray-500">Powered by UiTM • Secure & Reliable</p>
    </div>

    {{-- RIGHT --}}
    <div class="max-w-md mx-auto w-full bg-white rounded-2xl shadow-xl overflow-hidden">

        <div class="bg-gradient-to-r from-purple-600 to-purple-800 p-6 text-white">
            <h2 class="text-3xl font-bold">Reset Password</h2>
            <p class="text-purple-100">Enter your email to receive a password reset link</p>
        </div>

        @if (session('status'))
            <div class="m-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="p-6 space-y-5">
            @csrf

            {{-- Email --}}
            <div>
                <label class="text-sm">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="w-full mt-1 p-3 border rounded-xl @error('email') border-red-500 @enderror" required>
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full py-3 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-all duration-300">
                Send Reset Link
            </button>
        </form>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <p class="text-sm text-gray-600">Remember your password? <a href="{{ route('login') }}" class="text-purple-600 font-semibold hover:text-purple-800">Back to Login</a></p>
        </div>
    </div>
</div>

</body>
</html>
