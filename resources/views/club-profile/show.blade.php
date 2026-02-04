<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Profile | Campus Event Hub</title>
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
                <a href="{{ route('club-profile.show') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-purple-500 hover:bg-purple-400 transition text-sm font-medium">
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
                <a href="{{ route('instagram.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
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
                <h2 class="text-2xl font-bold text-gray-800">Club Profile</h2>
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

            <!-- Club Profile Content -->
            <div class="p-8">
                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Background Photo Section (Full Width) -->
                <div class="mb-8 -mx-8">
                    @if($club->background_photo)
                        @php
                            $posV = 50 + (($club->background_position_v ?? 0) / 2);
                        @endphp
                        <div style="height: 256px; background-image: url('{{ asset('storage/' . $club->background_photo) }}'); background-position: 50% {{ $posV }}%; background-size: cover; background-repeat: no-repeat;"></div>
                    @else
                        <div class="bg-gradient-to-r from-purple-500 to-purple-700 flex items-center justify-center text-5xl opacity-50" style="height: 256px;">
                            <span class="text-6xl opacity-50">📸</span>
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-3 gap-8 mb-8">
                    <!-- Club Card -->
                    <div class="col-span-1 bg-white rounded-lg shadow p-6 text-center">
                        @if($club->profile_photo)
                            <img src="{{ asset('storage/' . $club->profile_photo) }}" alt="{{ $club->name }}" class="w-24 h-24 mx-auto mb-4 rounded-full object-cover border-4 border-purple-600">
                        @else
                            <div class="w-24 h-24 bg-gradient-to-br from-purple-500 to-purple-700 rounded-full mx-auto mb-4 flex items-center justify-center text-4xl">
                                🏢
                            </div>
                        @endif
                        <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $club->name ?? 'Not Set' }}</h3>
                        <p class="text-sm text-gray-500 mb-4">{{ $club->category ?? 'No Category' }}</p>
                        <div class="space-y-2 text-sm text-gray-600 mb-6">
                            <p>📧 {{ $club->email ?? 'No Email' }}</p>
                            <p>👥 {{ $club->total_members ?? 0 }} Members</p>
                        </div>
                        <a href="{{ route('club-profile.edit') }}" class="inline-block px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                            Edit Profile
                        </a>
                    </div>

                    <!-- Club Information -->
                    <div class="col-span-2 bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-6">Club Information</h3>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Club Name</label>
                                <p class="text-gray-800">{{ $club->name ?? 'Not Set' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Category</label>
                                <p class="text-gray-800">{{ $club->category ?? 'Not Set' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                                <p class="text-gray-800">{{ $club->email ?? 'Not Set' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Total Members</label>
                                <p class="text-gray-800">{{ $club->total_members ?? 0 }}</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                            <p class="text-gray-700">{{ $club->description ?? 'No Description' }}</p>
                        </div>
                    </div>
                </div>

                <!-- President & Social Media -->
                <div class="grid grid-cols-2 gap-8">
                    <!-- President Information -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">President Information</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">President Name</label>
                                <p class="text-gray-800">{{ $club->president_name ?? 'Not Set' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Contact Number</label>
                                <p class="text-gray-800">{{ $club->president_contact ?? 'Not Set' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media Links -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Social Media Links</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="text-sm font-semibold text-gray-700">📘 Facebook</label>
                                <p class="text-sm text-gray-600">{{ $club->facebook_url ?? 'Not Set' }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-gray-700">📷 Instagram</label>
                                <p class="text-sm text-gray-600">{{ $club->instagram_url ?? 'Not Set' }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-gray-700">𝕏 Twitter</label>
                                <p class="text-sm text-gray-600">{{ $club->twitter_url ?? 'Not Set' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instagram Auto-Posting Status -->
                <div class="bg-white rounded-lg shadow p-6 mt-8">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">📱 Instagram Auto-Posting</h3>
                    
                    @php
                        $instagramAccount = $club->instagramAccount;
                        $isConnected = $instagramAccount && $instagramAccount->isTokenValid();
                    @endphp

                    <div class="p-4 rounded-lg border-2 {{ $isConnected ? 'border-green-300 bg-green-50' : 'border-red-300 bg-red-50' }}">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold {{ $isConnected ? 'text-green-800' : 'text-red-800' }}">
                                    {{ $isConnected ? '✅ Connected' : '❌ Not Connected' }}
                                </p>
                                @if($isConnected)
                                    <p class="text-xs {{ 'text-green-700' }} mt-2">
                                        <strong>Account:</strong> {{ $instagramAccount->instagram_username }}
                                    </p>
                                    <p class="text-xs {{ 'text-green-700' }} mt-1">
                                        <strong>Last post:</strong> {{ $instagramAccount->last_post_at ? $instagramAccount->last_post_at->diffForHumans() : 'Never' }}
                                    </p>
                                @else
                                    <p class="text-xs {{ 'text-red-700' }} mt-2">
                                        No Instagram account configured for automatic event posting.
                                    </p>
                                @endif
                            </div>
                            <a href="{{ route('club-profile.edit') }}" class="px-4 py-2 bg-purple-600 text-white rounded text-sm font-semibold hover:bg-purple-700 transition">
                                ⚙️ Configure
                            </a>
                        </div>
                    </div>

                    @if($isConnected)
                        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-xs text-blue-800">
                                ✨ <strong>Event posters will be automatically posted to your Instagram account</strong> when you create new events with featured images.
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Club Events Section -->
                <div class="bg-white rounded-lg shadow p-6 mt-8">
                    <h3 class="text-lg font-bold text-gray-800 mb-6">📋 Club Events</h3>
                    
                    <!-- Controls Row -->
                    <div class="flex flex-col sm:flex-row gap-4 mb-6">
                        <!-- Search Input -->
                        <input 
                            type="text" 
                            id="searchInput" 
                            placeholder="🔍 Search events..." 
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                        >
                        
                        <!-- Year Filter -->
                        <select 
                            id="yearSelect" 
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 bg-white"
                        >
                            <option value="">All Years</option>
                            @foreach($years as $year)
                                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                        
                        <!-- Clear Button -->
                        <button 
                            id="clearFilters" 
                            class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-semibold"
                        >
                            Clear
                        </button>
                    </div>

                    <!-- Events Grid -->
                    @if($events->count() > 0)
                        <div id="eventsContainer" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($events as $event)
                                @php
                                    $eventYear = $event->date->format('Y');
                                    $eventMonth = $event->date->format('M d, Y');
                                    $eventTime = $event->date->format('H:i');
                                @endphp
                                <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition event-card" data-name="{{ strtolower($event->name) }}" data-year="{{ $eventYear }}">
                                    @if($event->featured_image)
                                        <img src="{{ asset('storage/' . $event->featured_image) }}" alt="{{ $event->name }}" class="w-full h-40 object-cover">
                                    @else
                                        <div class="w-full h-40 bg-gradient-to-br from-purple-300 to-purple-500 flex items-center justify-center text-4xl">
                                            📅
                                        </div>
                                    @endif
                                    
                                    <div class="p-4">
                                        <div class="flex items-start justify-between mb-2">
                                            <h4 class="font-bold text-gray-800 flex-1 text-sm">{{ $event->name }}</h4>
                                            @if($event->instagram_posted_at)
                                                <span class="bg-pink-100 text-pink-800 text-xs px-2 py-1 rounded">📷 Posted</span>
                                            @endif
                                        </div>
                                        
                                        <p class="text-xs text-gray-600 mb-3">{{ Str::limit($event->description, 80) }}</p>
                                        
                                        <div class="space-y-1 text-xs text-gray-600 mb-4">
                                            <p>📅 {{ $eventMonth }}</p>
                                            <p>⏰ {{ $eventTime }}</p>
                                            <p>📍 {{ $event->venue ?? 'TBA' }}</p>
                                        </div>
                                        
                                        <a href="{{ route('event.show', $event->id) }}" class="text-purple-600 hover:text-purple-800 text-sm font-semibold">
                                            View Details →
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- No Results Message -->
                        <div id="noResults" class="text-center py-8 text-gray-600 hidden">
                            <p class="text-lg font-semibold">No events found</p>
                            <p class="text-sm">Try adjusting your filters</p>
                        </div>
                    @else
                        <div class="text-center py-12 text-gray-500">
                            <p class="text-lg font-semibold mb-2">📭 No Events Yet</p>
                            <p class="text-sm">This club hasn't created any events yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    <script>
        // Event filtering
        const searchInput = document.getElementById('searchInput');
        const yearSelect = document.getElementById('yearSelect');
        const clearFilters = document.getElementById('clearFilters');
        const eventCards = document.querySelectorAll('.event-card');
        const noResults = document.getElementById('noResults');
        const eventsContainer = document.getElementById('eventsContainer');

        function filterEvents() {
            const searchTerm = (searchInput?.value || '').toLowerCase();
            const year = yearSelect?.value;
            let visibleCount = 0;

            eventCards.forEach(card => {
                const eventName = (card.dataset.name || '').toLowerCase();
                const eventYear = card.dataset.year;
                
                const matchesSearch = eventName.includes(searchTerm);
                const matchesYear = !year || eventYear === year;
                const isVisible = matchesSearch && matchesYear;

                card.style.display = isVisible ? 'block' : 'none';
                if (isVisible) visibleCount++;
            });

            if (noResults) {
                noResults.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        }

        searchInput?.addEventListener('input', filterEvents);
        yearSelect?.addEventListener('change', filterEvents);

        clearFilters?.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            if (yearSelect) yearSelect.value = '';
            filterEvents();
        });
    </script>
</body>
</html>
