<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Registration | Campus Event Hub</title>
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-purple-50 flex items-center justify-center p-4">

<div class="w-full max-w-2xl">
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">

        <div class="bg-gradient-to-r from-purple-600 to-purple-800 p-6 text-white">
            <h2 class="text-3xl font-bold">Student Registration</h2>
            <p class="text-purple-100">Create your account to discover and attend events</p>
        </div>

        @if (session('success'))
            <div class="m-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('student-register.submit') }}" class="p-6 space-y-5">
            @csrf

            {{-- Full Name --}}
            <div>
                <label class="text-sm font-semibold">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="w-full mt-1 p-3 border rounded-xl @error('name') border-red-500 @enderror" 
                       placeholder="John Doe" required>
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <label class="text-sm font-semibold">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="w-full mt-1 p-3 border rounded-xl @error('email') border-red-500 @enderror" 
                       placeholder="your.email@student.uitm.edu.my" required>
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Phone Number --}}
            <div>
                <label class="text-sm font-semibold">Phone Number</label>
                <input type="tel" name="phone" value="{{ old('phone') }}"
                       class="w-full mt-1 p-3 border rounded-xl @error('phone') border-red-500 @enderror" 
                       placeholder="+60123456789" required>
                @error('phone')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Student ID --}}
            <div>
                <label class="text-sm font-semibold">Student ID</label>
                <input type="text" name="student_id" value="{{ old('student_id') }}"
                       class="w-full mt-1 p-3 border rounded-xl @error('student_id') border-red-500 @enderror" 
                       placeholder="2024XXXXX" required>
                @error('student_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Address --}}
            <div>
                <label class="text-sm font-semibold">Address</label>
                <input type="text" name="address" value="{{ old('address') }}"
                       class="w-full mt-1 p-3 border rounded-xl @error('address') border-red-500 @enderror" 
                       placeholder="Street address" required>
                @error('address')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- City --}}
            <div>
                <label class="text-sm font-semibold">City</label>
                <input type="text" name="city" value="{{ old('city') }}"
                       class="w-full mt-1 p-3 border rounded-xl @error('city') border-red-500 @enderror" 
                       placeholder="Shah Alam" required>
                @error('city')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Postal Code --}}
            <div>
                <label class="text-sm font-semibold">Postal Code</label>
                <input type="text" name="postal_code" value="{{ old('postal_code') }}"
                       class="w-full mt-1 p-3 border rounded-xl @error('postal_code') border-red-500 @enderror" 
                       placeholder="40000" required>
                @error('postal_code')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div>
                <label class="text-sm font-semibold">Password</label>
                <input type="password" name="password"
                       class="w-full mt-1 p-3 border rounded-xl @error('password') border-red-500 @enderror" 
                       placeholder="Minimum 8 characters" required>
                @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div>
                <label class="text-sm font-semibold">Confirm Password</label>
                <input type="password" name="password_confirmation"
                       class="w-full mt-1 p-3 border rounded-xl" 
                       placeholder="Re-enter your password" required>
            </div>

            <button type="submit" class="w-full py-3 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-all duration-300 font-semibold">
                Create Account
            </button>
        </form>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <p class="text-sm text-gray-600">Already have an account? <a href="{{ route('login') }}" class="text-purple-600 font-semibold hover:text-purple-800">Login here</a></p>
        </div>
    </div>
</div>

</body>
</html>
