<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instagram Management | Campus Event Hub</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-48 bg-gradient-to-b from-purple-600 to-purple-800 text-white overflow-y-auto flex flex-col">
            <div class="p-6 border-b border-purple-500">
                <h1 class="text-xl font-bold">Campus Event Hub</h1>
                <p class="text-sm text-purple-200">Club Admin Panel</p>
            </div>

            <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('club-profile.show') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>👥</span>
                    <span>Club Profile</span>
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-4 text-xs text-purple-300 uppercase font-semibold tracking-wide">Manage Event</p>
                </div>
                <a href="{{ route('event.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>📋</span>
                    <span>Event List</span>
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-4 text-xs text-purple-300 uppercase font-semibold tracking-wide">Social Media</p>
                </div>
                <a href="{{ route('instagram.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-purple-500 hover:bg-purple-400 transition text-sm font-medium">
                    <span>📷</span>
                    <span>Instagram</span>
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-4 text-xs text-purple-300 uppercase font-semibold tracking-wide">Manage Shop</p>
                </div>
                <a href="{{ route('merchandise.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>👕</span>
                    <span>Merchandise</span>
                </a>
                <a href="{{ route('orders.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>📦</span>
                    <span>Orders</span>
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-4 text-xs text-purple-300 uppercase font-semibold tracking-wide">More</p>
                </div>
                <a href="{{ route('club-profile.edit') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>⚙️</span>
                    <span>Settings</span>
                </a>
            </nav>

            <div class="p-3 border-t border-purple-500">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg bg-red-600 hover:bg-red-700 transition text-sm font-medium">
                        <span>🚪</span>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Instagram Management</h1>
                <p class="text-gray-600 mt-2">Manage and post your events to Instagram</p>
            </div>
            <a href="{{ route('instagram.settings') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center gap-2">
                <i class="fas fa-cog"></i> Settings
            </a>
        </div>
    </div>

    <!-- Status Alert -->
    @if(!$hasCredentials)
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-yellow-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-800">
                    Instagram credentials not configured. Please add your credentials in 
                    <a href="{{ route('instagram.settings') }}" class="font-semibold underline">Settings</a>.
                </p>
            </div>
        </div>
    </div>
    @else
    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-8">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-800">
                    Instagram credentials configured and ready to post!
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Success/Error Messages -->
    @if($message = session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-check-circle"></i> {{ $message }}
    </div>
    @endif

    @if($message = session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-exclamation-circle"></i> {{ $message }}
    </div>
    @endif

    <!-- Events Grid -->
    <div class="grid grid-cols-1 gap-6">
        <h2 class="text-2xl font-semibold text-gray-900">Your Events</h2>

        @if($events->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($events as $event)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                    <!-- Event Image -->
                    @if($event->event_image)
                    <div class="h-48 bg-gray-200 overflow-hidden">
                        <img src="{{ asset('storage/' . $event->event_image) }}" 
                             alt="{{ $event->name }}" 
                             class="w-full h-full object-cover">
                    </div>
                    @else
                    <div class="h-48 bg-gray-200 flex items-center justify-center">
                        <i class="fas fa-image text-gray-400 text-4xl"></i>
                    </div>
                    @endif

                    <!-- Event Details -->
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $event->name }}</h3>
                        
                        <div class="space-y-2 text-sm text-gray-600 mb-4">
                            <p><i class="fas fa-calendar text-blue-600"></i> {{ $event->date->format('M d, Y') }}</p>
                            <p><i class="fas fa-map-marker-alt text-blue-600"></i> {{ $event->location }}</p>
                            <p><i class="fas fa-tag text-blue-600"></i> {{ $event->category }}</p>
                        </div>

                        <!-- Status Badge -->
                        <div class="mb-4">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold
                                @if($event->status === 'Upcoming')
                                    bg-blue-100 text-blue-800
                                @elseif($event->status === 'Currently Running')
                                    bg-green-100 text-green-800
                                @else
                                    bg-gray-100 text-gray-800
                                @endif
                            ">
                                {{ $event->status }}
                            </span>
                        </div>

                        <!-- Post Button -->
                        @if($event->event_image && $hasCredentials)
                        <form action="{{ route('instagram.post-event', $event) }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit" 
                                    class="w-full bg-gradient-to-r from-pink-500 via-red-500 to-yellow-500 hover:from-pink-600 hover:via-red-600 hover:to-yellow-600 text-white font-semibold py-2 px-4 rounded-lg transition flex items-center justify-center gap-2">
                                <i class="fab fa-instagram"></i> Post to Instagram
                            </button>
                        </form>
                        @elseif(!$event->event_image)
                        <button disabled class="w-full bg-gray-300 text-gray-600 font-semibold py-2 px-4 rounded-lg cursor-not-allowed">
                            <i class="fas fa-image"></i> No Image
                        </button>
                        @else
                        <button disabled class="w-full bg-gray-300 text-gray-600 font-semibold py-2 px-4 rounded-lg cursor-not-allowed">
                            <i class="fas fa-cog"></i> Configure First
                        </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @else
        <div class="bg-gray-50 rounded-lg p-8 text-center">
            <i class="fas fa-calendar-alt text-gray-400 text-5xl mb-4"></i>
            <p class="text-gray-600 text-lg">No events created yet</p>
            <a href="{{ route('event.create') }}" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                Create First Event
            </a>
        </div>
        @endif
    </div>
        </main>
    </div>
</body>
</html>
