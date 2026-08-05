<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="{{ asset('images/uitm_logo.png') }}?v={{ filemtime(public_path('images/uitm_logo.png')) }}">
    <title>Reset Password | Campus Event Hub</title>
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
            <h2 class="text-3xl font-bold">Create New Password</h2>
            <p class="text-purple-100">Enter your new password</p>
        </div>

        <form method="POST" action="{{ route('password.store') }}" class="p-6 space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            {{-- Email --}}
            <div>
                <label class="text-sm">Email Address</label>
                <input type="email" name="email" value="{{ old('email', $request->email) }}"
                       class="w-full mt-1 p-3 border rounded-xl @error('email') border-red-500 @enderror" required>
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div>
                <label class="text-sm">New Password</label>
                <input type="password" name="password"
                       class="w-full mt-1 p-3 border rounded-xl @error('password') border-red-500 @enderror" required>
                @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div>
                <label class="text-sm">Confirm Password</label>
                <input type="password" name="password_confirmation"
                       class="w-full mt-1 p-3 border rounded-xl @error('password_confirmation') border-red-500 @enderror" required>
                @error('password_confirmation')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full py-3 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-all duration-300">
                Reset Password
            </button>
        </form>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <p class="text-sm text-gray-600"><a href="{{ route('login') }}" class="text-purple-600 font-semibold hover:text-purple-800">Back to Login</a></p>
        </div>
    </div>
</div>

</body>
</html>
