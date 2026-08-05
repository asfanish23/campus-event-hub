<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="{{ asset('images/uitm_logo.png') }}?v={{ filemtime(public_path('images/uitm_logo.png')) }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Student Dashboard | Campus Event Hub</title>
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
            <a href="{{ route('student.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-purple-500 hover:bg-purple-400 transition text-sm font-medium">
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

            <a href="{{ route('student.clubs') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm font-medium">
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

            {{-- Welcome --}}
            <div class="bg-gradient-to-r from-purple-600 to-purple-800 text-white rounded-2xl px-10 py-7 mb-8">
                <h2 class="text-4xl font-bold">Welcome, {{ auth()->user()->name }}!</h2>
            </div>

            {{-- Recommended Events Section --}}
            @if($recommendedEvents->count() > 0)
                <div class="bg-gradient-to-r from-amber-50 to-orange-50 rounded-2xl shadow-md border border-amber-200 mb-8">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <h3 class="text-2xl font-bold text-gray-900">✨ Recommended For You</h3>
                            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-amber-200 text-amber-800">
                                {{ $recommendedEvents->count() }} suggestions
                            </span>
                        </div>
                        <p class="text-gray-600 mb-6">Based on your interests and event preferences:</p>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                            @foreach($recommendedEvents as $event)
                                @php
                                    $badge = strtolower($event->status ?? '');
                                    $badgeClass = 'bg-purple-100 text-purple-800';
                                    if ($badge === 'ongoing') $badgeClass = 'bg-green-100 text-green-800';
                                    if ($badge === 'completed') $badgeClass = 'bg-gray-100 text-gray-700';
                                @endphp
                                <a href="{{ route('student.event.show', $event->id) }}" class="group">
                                    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg hover:border-purple-300 transition h-full flex flex-col">
                                        {{-- Image --}}
                                        <div class="relative">
                                            @if($event->event_image)
                                                <img src="{{ asset('storage/' . $event->event_image) }}" alt="{{ $event->name }}" class="w-full h-32 object-cover">
                                            @else
                                                <div class="w-full h-32 bg-purple-100 flex items-center justify-center text-2xl">📅</div>
                                            @endif
                                            <div class="absolute top-2 left-2">
                                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                                    {{ ucfirst($event->status) }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        {{-- Content --}}
                                        <div class="p-3 flex-1 flex flex-col">
                                            <h4 class="font-bold text-sm text-gray-900 group-hover:text-purple-700 transition line-clamp-2">
                                                {{ $event->name }}
                                            </h4>
                                            
                                            <div class="mt-2 text-xs text-gray-600 space-y-1">
                                                <p class="flex items-center gap-1">
                                                    <span>📅</span>
                                                    <span>{{ optional($event->date)->format('M d') ?? 'TBA' }}</span>
                                                </p>
                                                <p class="flex items-center gap-1">
                                                    <span>📍</span>
                                                    <span class="truncate">{{ $event->location ?? 'TBA' }}</span>
                                                </p>
                                            </div>
                                            
                                            <div class="mt-3 pt-3 border-t border-gray-100">
                                                <button onclick="event.preventDefault(); event.stopPropagation(); toggleLike({{ $event->id }})"
                                                    class="like-btn-{{ $event->id }} w-full px-2 py-1 rounded text-xs font-semibold transition {{ in_array($event->id, $likedEventIds) ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                                    <span class="like-icon-{{ $event->id }}">{{ in_array($event->id, $likedEventIds) ? '❤️' : '🤍' }}</span>
                                                    <span class="like-count-{{ $event->id }}">{{ $event->likes()->count() }}</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-blue-50 rounded-2xl shadow-md border border-blue-200 mb-8 p-6">
                    <div class="flex gap-4">
                        <div class="text-3xl">💡</div>
                        <div>
                            <h3 class="font-bold text-gray-900 mb-1">Start Liking Events</h3>
                            <p class="text-gray-600 text-sm">Like events you're interested in to get personalized recommendations based on your preferences!</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Events --}}
            <div class="bg-white rounded-2xl shadow-md border border-gray-100">
                <div class="p-6 pb-4">
                    {{-- Title row --}}
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <h3 class="text-2xl font-bold text-gray-900">Events</h3>
                            <span id="resultCount"
                                  class="shrink-0 text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-600">
                            </span>
                        </div>

                        <button id="clearFilters" type="button"
                                class="shrink-0 text-sm font-semibold text-purple-700 hover:text-purple-900">
                            Clear
                        </button>
                    </div>

                    {{-- Controls row (wrapped nicely) --}}
                    <div class="mt-4 bg-gray-50 border border-gray-100 rounded-xl p-3">
                        <div class="flex flex-wrap items-center gap-3">
                            {{-- Search --}}
                            <div class="relative flex-1 min-w-[220px]">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">🔎</span>
                                <input
                                    id="eventSearch"
                                    type="text"
                                    placeholder="Search events..."
                                    class="w-full h-10 pl-9 pr-3 border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-purple-300"
                                />
                            </div>

                            {{-- Club --}}
                            <select id="clubFilter"
                                    class="h-10 w-full sm:w-[170px] px-3 border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-purple-300">
                                <option value="all">All Clubs</option>
                                @foreach($clubs as $club)
                                    <option value="{{ $club->id }}">{{ $club->name }}</option>
                                @endforeach
                            </select>

                            {{-- Category --}}
                            <select id="categoryFilter"
                                    class="h-10 w-full sm:w-[170px] px-3 border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-purple-300">
                                <option value="all">All Categories</option>
                                @php
                                    $categories = $upcomingEvents->pluck('category')->filter()->map(fn($c) => strtolower($c))->unique()->values();
                                @endphp
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                                @endforeach
                            </select>

                            {{-- Status --}}
                            <select id="statusFilter"
                                    class="h-10 w-full sm:w-[160px] px-3 border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-purple-300">
                                <option value="all">All Status</option>
                                <option value="upcoming">Upcoming</option>
                                <option value="ongoing">Ongoing</option>
                            </select>

                            {{-- Sort --}}
                            <select id="sortSelect"
                                    class="h-10 w-full sm:w-[190px] px-3 border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-purple-300">
                                <option value="date_asc">Sort: Soonest</option>
                                <option value="date_desc">Sort: Latest</option>
                                <option value="name_asc">Sort: Name A–Z</option>
                                <option value="name_desc">Sort: Name Z–A</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Grid --}}
                <div class="px-6 pb-6">
                    @if($upcomingEvents->count() > 0)
                        <div id="eventsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($upcomingEvents as $event)
                                @php
                                    $eventName = $event->name ?? '';
                                    $catLower = strtolower($event->category ?? '');
                                    $statusLower = strtolower($event->status ?? '');
                                    $dateIso = optional($event->date)->format('Y-m-d') ?? '';
                                    $timeIso = optional($event->start_time)->format('H:i') ?? '';
                                    $dateTimeIso = ($dateIso && $timeIso) ? ($dateIso.'T'.$timeIso) : $dateIso;

                                    $clubId = $event->club_id ?? optional($event->club)->id ?? '';

                                    $badge = strtolower($event->status ?? '');
                                    $badgeClass = 'bg-purple-100 text-purple-800';
                                    if ($badge === 'ongoing') $badgeClass = 'bg-green-100 text-green-800';
                                    if ($badge === 'completed') $badgeClass = 'bg-gray-100 text-gray-700';
                                @endphp

                                <div
                                    class="event-card"
                                    data-href="{{ route('student.event.show', $event->id) }}"
                                    data-name="{{ strtolower($eventName) }}"
                                    data-club="{{ $clubId }}"
                                    data-category="{{ $catLower ?: 'uncategorized' }}"
                                    data-status="{{ $statusLower ?: 'unknown' }}"
                                    data-date="{{ $dateIso }}"
                                    data-datetime="{{ $dateTimeIso }}"
                                    data-start-time="{{ optional($event->start_time)->format('H:i:s') }}"
                                    data-end-time="{{ optional($event->end_time)->format('H:i:s') }}"
                                >
                                    <div class="group bg-white border border-gray-200 rounded-2xl overflow-hidden hover:shadow-lg hover:border-purple-300 transition cursor-pointer h-full flex flex-col">
                                        {{-- Poster --}}
                                        <div class="relative">
                                            @if($event->event_image)
                                                <img src="{{ asset('storage/' . $event->event_image) }}"
                                                     alt="{{ $event->name }}"
                                                     class="w-full h-40 object-cover">
                                            @else
                                                <div class="w-full h-40 bg-purple-100 flex items-center justify-center text-4xl">📅</div>
                                            @endif

                                            {{-- Badge --}}
                                            <div class="absolute top-3 left-3">
                                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                                    {{ ucfirst($event->status) }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Content --}}
                                        <div class="p-4 flex-1 flex flex-col">
                                            <h4 class="font-bold text-lg text-gray-900 group-hover:text-purple-700 transition line-clamp-2">
                                                {{ $event->name }}
                                            </h4>

                                            <div class="mt-2 text-sm text-gray-600 space-y-1">
                                                <p class="flex items-center gap-2">
                                                    <span class="text-gray-400">📅</span>
                                                    <span>{{ optional($event->date)->format('D, M d') ?? 'TBA' }} • {{ optional($event->start_time)->format('H:i') ?? 'TBA' }}</span>
                                                </p>
                                                <p class="flex items-center gap-2">
                                                    <span class="text-gray-400">📍</span>
                                                    <span class="truncate">{{ $event->location ?? 'TBA' }}</span>
                                                </p>
                                                <p class="flex items-center gap-2">
                                                    <span class="text-gray-400">🏷</span>
                                                    <span class="inline-flex items-center px-2 py-0.5 bg-gray-100 rounded text-xs">
                                                        {{ ucfirst($event->category) }}
                                                    </span>
                                                </p>
                                            </div>

                                            <div class="mt-4 flex items-center justify-between gap-3">
                                                <button
                                                    onclick="event.stopPropagation(); toggleLike({{ $event->id }})"
                                                    class="like-btn-{{ $event->id }} flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-semibold transition {{ in_array($event->id, $likedEventIds) ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                                                >
                                                    <span class="like-icon-{{ $event->id }}">{{ in_array($event->id, $likedEventIds) ? '❤️' : '🤍' }}</span>
                                                    <span class="like-count-{{ $event->id }}">{{ $event->likes()->count() }}</span>
                                                </button>

                                                @if(in_array($event->id, $registeredEventIds))
                                                    <button
                                                        onclick="event.stopPropagation(); cancelRegistration({{ $event->id }})"
                                                        class="register-btn-{{ $event->id }} px-4 py-2 bg-green-600 hover:bg-red-600 text-white rounded-lg text-sm font-semibold whitespace-nowrap transition"
                                                    >
                                                        <span class="register-text-{{ $event->id }}">✓ Registered</span>
                                                    </button>
                                                @else
                                                    <button
                                                        onclick="event.stopPropagation(); registerEvent({{ $event->id }})"
                                                        class="register-btn-{{ $event->id }} px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm font-semibold whitespace-nowrap"
                                                    >
                                                        Register
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- No results --}}
                        <div id="noResults" class="hidden text-center py-12">
                            <p class="text-3xl mb-3">🔎</p>
                            <p class="text-gray-500 text-lg">No events match your filters</p>
                            <p class="text-gray-400 text-sm mt-2">Try different keywords or clear filters.</p>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <p class="text-3xl mb-3">📭</p>
                            <p class="text-gray-500 text-lg">No upcoming events at the moment</p>
                            <p class="text-gray-400 text-sm mt-2">Check back soon for new events!</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </main>
</div>

<script>
    // Function to calculate actual event status based on current date/time
    function getActualStatus(dateStr, startTimeStr, endTimeStr) {
        const today = new Date();
        const eventDate = new Date(dateStr);
        
        // Normalize today to midnight for date comparison
        const todayMidnight = new Date(today.getFullYear(), today.getMonth(), today.getDate());
        
        // If event date is in the past (before today)
        if (eventDate < todayMidnight) {
            return 'Completed';
        }

        // If event date is today
        if (eventDate.getTime() === todayMidnight.getTime()) {
            // Check if event has started and not ended
            if (startTimeStr) {
                const eventStart = new Date(`${dateStr}T${startTimeStr}`);
                if (eventStart <= today) {
                    if (!endTimeStr) {
                        return 'Currently Running';
                    }
                    const eventEnd = new Date(`${dateStr}T${endTimeStr}`);
                    if (eventEnd > today) {
                        return 'Currently Running';
                    } else {
                        return 'Completed';
                    }
                }
            }
        }

        // Otherwise it's upcoming
        return 'Upcoming';
    }

    // Update status badges on load
    function updateStatusBadges() {
        document.querySelectorAll('.event-card').forEach(card => {
            const dateStr = card.dataset.date;
            const startTime = card.dataset.startTime;
            const endTime = card.dataset.endTime;
            
            if (dateStr) {
                const actualStatus = getActualStatus(dateStr, startTime, endTime);
                const badge = card.querySelector('[class*="rounded-full text-xs font-semibold"]');
                
                if (badge) {
                    // Update text
                    badge.textContent = actualStatus;
                    
                    // Update color classes
                    badge.className = 'px-3 py-1 rounded-full text-xs font-semibold';
                    
                    if (actualStatus === 'Upcoming') {
                        badge.classList.add('bg-blue-100', 'text-blue-700');
                    } else if (actualStatus === 'Currently Running') {
                        badge.classList.add('bg-green-100', 'text-green-700');
                    } else if (actualStatus === 'Completed') {
                        badge.classList.add('bg-gray-100', 'text-gray-700');
                    }
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Update status badges based on current date/time
        updateStatusBadges();
        // Make card clickable (but keep buttons working)
        document.querySelectorAll('.event-card').forEach(card => {
            card.addEventListener('click', () => {
                const href = card.dataset.href;
                if (href) window.location.href = href;
            });
        });

        // Hover effect for registered buttons
        document.querySelectorAll('[class*="register-btn-"]').forEach(btn => {
            const eventId = btn.className.match(/register-btn-(\d+)/)?.[1];
            if (eventId && btn.classList.contains('bg-green-600')) {
                const textSpan = btn.querySelector(`[class*="register-text-"]`);
                if (textSpan) {
                    btn.addEventListener('mouseenter', () => textSpan.textContent = '✕ Cancel');
                    btn.addEventListener('mouseleave', () => textSpan.textContent = '✓ Registered');
                }
            }
        });

        // Filters + Sort (client-side)
        const searchInput = document.getElementById('eventSearch');
        const clubFilter = document.getElementById('clubFilter');
        const categoryFilter = document.getElementById('categoryFilter');
        const statusFilter = document.getElementById('statusFilter');
        const sortSelect = document.getElementById('sortSelect');
        const clearBtn = document.getElementById('clearFilters');

        const grid = document.getElementById('eventsGrid');
        const cards = Array.from(document.querySelectorAll('.event-card'));
        const resultCount = document.getElementById('resultCount');
        const noResults = document.getElementById('noResults');

        function normalize(v) { return (v || '').toString().trim().toLowerCase(); }

        function applyFiltersAndSort() {
            const q = normalize(searchInput?.value);
            const club = normalize(clubFilter?.value);
            const cat = normalize(categoryFilter?.value);
            const st = normalize(statusFilter?.value);
            const sort = normalize(sortSelect?.value);

            let visible = cards.filter(card => {
                const name = normalize(card.dataset.name);
                const clubId = normalize(card.dataset.club);
                const c = normalize(card.dataset.category);
                
                // Calculate actual status from date/time instead of using server data-status
                const dateStr = card.dataset.date;
                const startTime = card.dataset.startTime;
                const endTime = card.dataset.endTime;
                const actualStatus = dateStr ? getActualStatus(dateStr, startTime, endTime) : '';
                const s = normalize(actualStatus);

                const matchSearch = !q || name.includes(q);
                const matchClub = (club === 'all') || (clubId === club);
                const matchCat = (cat === 'all') || (c === cat);
                const matchStatus = (st === 'all') || (s === st);

                const show = matchSearch && matchClub && matchCat && matchStatus;
                card.classList.toggle('hidden', !show);
                return show;
            });

            visible.sort((a, b) => {
                const aName = normalize(a.dataset.name);
                const bName = normalize(b.dataset.name);
                const aDt = a.dataset.datetime || a.dataset.date || '';
                const bDt = b.dataset.datetime || b.dataset.date || '';

                if (sort === 'name_asc') return aName.localeCompare(bName);
                if (sort === 'name_desc') return bName.localeCompare(aName);
                if (sort === 'date_desc') return bDt.localeCompare(aDt);
                return aDt.localeCompare(bDt);
            });

            if (grid) visible.forEach(v => grid.appendChild(v));

            if (resultCount) {
                const total = cards.length;
                resultCount.textContent = `${visible.length} of ${total}`;
            }
            if (noResults) noResults.classList.toggle('hidden', visible.length !== 0);
        }

        applyFiltersAndSort();

        [searchInput, clubFilter, categoryFilter, statusFilter, sortSelect].forEach(el => {
            if (!el) return;
            el.addEventListener('input', applyFiltersAndSort);
            el.addEventListener('change', applyFiltersAndSort);
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                if (clubFilter) clubFilter.value = 'all';
                if (categoryFilter) categoryFilter.value = 'all';
                if (statusFilter) statusFilter.value = 'all';
                if (sortSelect) sortSelect.value = 'date_asc';
                applyFiltersAndSort();
            });
        }
    });

    async function registerEvent(eventId) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const response = await fetch(`/student/event/${eventId}/register`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' }
            });

            const data = await response.json();

            if (data.success) {
                alert('✓ ' + data.message);
                const btn = document.querySelector('.register-btn-' + eventId);
                if (btn) {
                    btn.parentElement.innerHTML = `
                        <button onclick="event.stopPropagation(); cancelRegistration(${eventId})"
                            class="register-btn-${eventId} px-4 py-2 bg-green-600 hover:bg-red-600 text-white rounded-lg text-sm font-semibold whitespace-nowrap transition">
                            <span class="register-text-${eventId}">✓ Registered</span>
                        </button>
                    `;
                    const newBtn = document.querySelector('.register-btn-' + eventId);
                    const textSpan = newBtn.querySelector('.register-text-' + eventId);
                    newBtn.addEventListener('mouseenter', () => textSpan.textContent = '✕ Cancel');
                    newBtn.addEventListener('mouseleave', () => textSpan.textContent = '✓ Registered');
                }
            } else {
                alert('✗ ' + (data.message || 'Registration failed'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error registering for event: ' + error.message);
        }
    }

    async function cancelRegistration(eventId) {
        if (!confirm('Are you sure you want to cancel your registration?')) return;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const response = await fetch(`/student/event/${eventId}/cancel-registration`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' }
            });

            const data = await response.json();

            if (data.success) {
                alert('✓ ' + data.message);
                const btn = document.querySelector('.register-btn-' + eventId);
                if (btn) {
                    btn.parentElement.innerHTML = `
                        <button onclick="event.stopPropagation(); registerEvent(${eventId})"
                            class="register-btn-${eventId} px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm font-semibold whitespace-nowrap">
                            Register
                        </button>
                    `;
                }
            } else {
                alert('✗ ' + (data.message || 'Cancellation failed'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error cancelling registration: ' + error.message);
        }
    }

    async function toggleLike(eventId) {
        const likeBtn = document.querySelector('.like-btn-' + eventId);
        const likeIcon = document.querySelector('.like-icon-' + eventId);
        const isLiked = likeIcon.textContent.includes('❤️');
        
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const endpoint = isLiked ? 'unlike' : 'like';
            const response = await fetch(`/student/event/${eventId}/${endpoint}`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' }
            });

            const data = await response.json();

            if (data.success) {
                // Update icon and count
                likeIcon.textContent = isLiked ? '🤍' : '❤️';
                document.querySelector('.like-count-' + eventId).textContent = data.likeCount;
                
                // Update button background
                if (isLiked) {
                    likeBtn.classList.remove('bg-red-100', 'text-red-700', 'hover:bg-red-200');
                    likeBtn.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                } else {
                    likeBtn.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                    likeBtn.classList.add('bg-red-100', 'text-red-700', 'hover:bg-red-200');
                }
            } else {
                alert('✗ ' + (data.message || 'Action failed'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error updating like: ' + error.message);
        }
    }
</script>

</body>
</html>
