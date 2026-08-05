<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Reviews | Campus Event Hub</title>
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
                <a href="{{ route('event.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-purple-500 hover:bg-purple-400 transition text-sm font-medium">
                    <span>📋</span>
                    <span>Event List</span>
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-4 text-xs text-purple-300 uppercase font-semibold tracking-wide">Social Media</p>
                </div>
                <a href="{{ route('instagram.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>📱</span>
                    <span>Social Media</span>
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-4 text-xs text-purple-300 uppercase font-semibold tracking-wide">Manage Shop</p>
                </div>
                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>👕</span>
                    <span>Merchandise</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>🛒</span>
                    <span>Orders</span>
                </a>
            </nav>

            <div class="px-3 py-2 space-y-1">
                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>⚙️</span>
                    <span>Settings</span>
                </a>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="p-3 border-t border-purple-500">
                @csrf
                <button type="submit" class="w-full px-4 py-3 bg-red-500 hover:bg-red-600 rounded-lg transition text-sm font-semibold">
                    🚪 Logout
                </button>
            </form>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-auto">
            <!-- Top Bar -->
            <div class="bg-white border-b border-gray-200 px-8 py-4 flex justify-between items-center sticky top-0 z-10">
                <div>
                    <a href="{{ route('event.index') }}" class="text-purple-600 hover:text-purple-700 text-sm mb-1">← Back to Events</a>
                    <h2 class="text-2xl font-bold text-gray-800">Reviews</h2>
                </div>
                <div class="flex items-center gap-6">
                    <button class="text-gray-600 text-xl">🔔</button>
                    <div class="flex items-center gap-3 pl-6 border-l border-gray-300">
                        <div class="text-right">
                            <p class="font-semibold text-gray-800">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500">Club President</p>
                        </div>
                        <div class="w-10 h-10 bg-purple-600 text-white rounded-full flex items-center justify-center font-bold">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reviews Content -->
            <div class="p-8">
                <p class="text-gray-600 mb-6">View and report inappropriate reviews</p>

                @if(session('success'))
                    <div class="mb-4 rounded-lg bg-green-100 text-green-800 px-4 py-3">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="mb-4 rounded-lg bg-red-100 text-red-800 px-4 py-3">{{ session('error') }}</div>
                @endif
                @if(session('info'))
                    <div class="mb-4 rounded-lg bg-blue-100 text-blue-800 px-4 py-3">{{ session('info') }}</div>
                @endif

                <!-- Filter Section -->
                <div class="mb-6 bg-white rounded-lg shadow p-6">
                    <form method="GET" action="{{ route('event.reviews', $event) }}" class="flex gap-4 items-end">
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Filter by Rating</label>
                            <select name="rating" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                <option value="">All Ratings</option>
                                <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>⭐⭐⭐⭐⭐ 5 Stars</option>
                                <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>⭐⭐⭐⭐ 4 Stars</option>
                                <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>⭐⭐⭐ 3 Stars</option>
                                <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>⭐⭐ 2 Stars</option>
                                <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>⭐ 1 Star</option>
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Search by Name</label>
                            <input type="text" name="search" placeholder="Search reviewer name..." value="{{ request('search') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">Filter</button>
                            <a href="{{ route('event.reviews', $event) }}" class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-semibold">Reset</a>
                        </div>
                    </form>
                </div>

                <!-- Reviews Section -->
                <div class="max-w-4xl">
                    <h3 class="text-xl font-bold text-gray-800 mb-6">Recent Reviews</h3>
                    
                    @forelse($reviews as $review)
                        <div class="bg-white rounded-lg shadow p-6 mb-6">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h4 class="text-lg font-bold text-gray-800">{{ $review->user->name ?? $review->reviewer_name }}</h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <div class="flex items-center">
                                            @for($i = 0; $i < $review->rating; $i++)
                                                <span class="text-yellow-400">⭐</span>
                                            @endfor
                                            @for($i = $review->rating; $i < 5; $i++)
                                                <span class="text-gray-300">⭐</span>
                                            @endfor
                                        </div>
                                        <span class="text-sm text-gray-500">{{ $review->rating }}/5</span>
                                    </div>
                                </div>
                                @if($review->is_reported)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">Reported</span>
                                @else
                                    <form method="POST" action="{{ route('event.reviews.report', ['event' => $event, 'review' => $review]) }}">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-red-100 hover:text-red-700 transition">🚩 Report</button>
                                    </form>
                                @endif
                            </div>

                            <p class="text-sm text-gray-600 mb-3">
                                {{ $event->name }} • {{ $event->date->format('Y-m-d') }}
                            </p>

                            <p class="text-gray-700 leading-relaxed">{{ $review->comment ?? $review->review_text }}</p>
                        </div>
                    @empty
                        <div class="bg-white rounded-lg shadow p-8 text-center">
                            <p class="text-gray-500">No reviews yet. Be the first to review!</p>
                        </div>
                    @endforelse
                </div>

                <!-- Review Statistics -->
                @if($reviews->count() > 0)
                    <div class="grid grid-cols-3 gap-6 mt-8">
                        <div class="bg-white rounded-lg shadow p-6">
                            <p class="text-gray-600 text-sm font-semibold mb-2">Average Rating</p>
                            <p class="text-4xl font-bold text-purple-600">{{ number_format($reviews->avg('rating'), 1) }}</p>
                            <p class="text-xs text-gray-500 mt-2">out of 5 stars</p>
                        </div>
                        <div class="bg-white rounded-lg shadow p-6">
                            <p class="text-gray-600 text-sm font-semibold mb-2">Total Reviews</p>
                            <p class="text-4xl font-bold text-gray-800">{{ $reviews->count() }}</p>
                            <p class="text-xs text-gray-500 mt-2">from participants</p>
                        </div>
                        <div class="bg-white rounded-lg shadow p-6">
                            <p class="text-gray-600 text-sm font-semibold mb-2">Most Common Rating</p>
                            <p class="text-4xl font-bold text-yellow-500">
                                @php
                                    $modes = $reviews->groupBy('rating')->map->count();
                                    $mostCommon = $modes->keys()->first();
                                @endphp
                                {{ $mostCommon }}⭐
                            </p>
                            <p class="text-xs text-gray-500 mt-2">most common rating</p>
                        </div>
                    </div>
                @endif
            </div>
        </main>
    </div>
</body>
</html>
