<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="{{ asset('images/uitm_logo.png') }}?v={{ filemtime(public_path('images/uitm_logo.png')) }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Clubs | Campus Event Hub</title>
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen bg-gray-50">

<div class="flex h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-gradient-to-b from-purple-600 to-purple-800 text-white overflow-y-auto flex flex-col">
        <div class="p-6 border-b border-purple-500">
            <h1 class="text-xl font-bold">Campus Event Hub</h1>
            <p class="text-xs text-purple-200">Student Portal</p>
        </div>

        <nav class="flex-1 px-3 py-6 space-y-2 overflow-y-auto">
            <a href="{{ route('student.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm font-medium">
                <span>🏠</span>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('student.calendar') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm font-medium">
                <span>📅</span>
                <span>Calendar</span>
            </a>

            <a href="{{ route('student.archive') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm font-medium">
                <span>📦</span>
                <span>Archive</span>
            </a>

            <a href="{{ route('student.clubs') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-purple-500 hover:bg-purple-400 transition text-sm font-medium">
                <span>🏛️</span>
                <span>Clubs</span>
            </a>

            <a href="{{ route('student.shop') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm font-medium">
                <span>🛍️</span>
                <span>Shop</span>
            </a>
        </nav>

        <div class="p-3 border-t border-purple-500 space-y-2">
            <a href="{{ route('student.profile.show') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-purple-500 transition text-sm font-semibold">
                👤 My Profile
            </a>

            <form action="{{ route('logout') }}" method="POST" class="w-full">
                @csrf
                <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-semibold">
                    🚪 Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto bg-gray-50">
        <div class="max-w-7xl mx-auto px-6 py-8">

            <!-- Header -->
            <div class="bg-gradient-to-r from-purple-600 to-purple-800 text-white rounded-2xl px-10 py-7 mb-8">
                <h2 class="text-4xl font-bold">🏛️ Clubs</h2>
                <p class="text-purple-100 mt-1">Explore all campus clubs</p>
            </div>

            <!-- Clubs Grid -->
            <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6">
                @if($allClubs->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($allClubs as $club)
                            <a href="{{ route('student.club.show', $club->id) }}" class="group">
                                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl overflow-hidden hover:shadow-lg transition border border-gray-200 h-full flex flex-col">
                                    
                                    <!-- Club Cover/Image -->
                                    <div class="h-40 bg-gradient-to-r from-purple-400 to-purple-600 flex items-center justify-center relative overflow-hidden">
                                        @if($club->profile_photo)
                                            <img src="{{ asset('storage/' . $club->profile_photo) }}" alt="{{ $club->name }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-6xl">🏛️</span>
                                        @endif
                                        
                                        <!-- Overlay on hover -->
                                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition duration-300"></div>
                                    </div>

                                    <!-- Club Info -->
                                    <div class="p-6 flex-1 flex flex-col">
                                        <h3 class="text-xl font-bold text-gray-900 group-hover:text-purple-700 transition mb-4 line-clamp-2">
                                            {{ $club->name }}
                                        </h3>

                                        <!-- Button -->
                                        <div class="mt-auto pt-4 border-t border-gray-200">
                                            <button class="w-full py-2 px-4 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold text-sm">
                                                View Club →
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <p class="text-3xl mb-3">🏛️</p>
                        <p class="text-gray-500 text-lg">No clubs available</p>
                        <p class="text-gray-400 text-sm mt-2">Check back soon for new clubs!</p>
                    </div>
                @endif
            </div>

        </div>
    </main>
</div>

</body>
</html>
