<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Campus Event Hub')</title>
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen bg-gray-50">

    {{-- Navigation Bar --}}
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="{{ route('student.dashboard') }}" class="flex items-center gap-2 text-purple-600 hover:text-purple-800 font-semibold">
                    ← Back
                </a>
                <a href="{{ route('student.dashboard') }}" class="text-2xl font-bold text-purple-600">Campus Event Hub</a>
            </div>
            <div class="flex items-center gap-6">
                <span class="text-gray-700">Welcome, <strong>{{ auth()->user()->name }}</strong></span>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        🚪 Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-gray-800 text-white py-6 mt-12">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <p>&copy; 2026 Campus Event Hub. All rights reserved.</p>
        </div>
    </footer>

    @vite('resources/js/app.js')
</body>
</html>
