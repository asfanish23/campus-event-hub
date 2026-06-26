<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Archive | Campus Event Hub</title>
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

            <a href="{{ route('student.archive') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-purple-500 hover:bg-purple-400 transition text-sm font-medium">
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
                <h2 class="text-4xl font-bold">📦 Event Archive</h2>
                <p class="text-purple-100 mt-1">View all completed events</p>
            </div>

            {{-- Events Grid --}}
            <div class="bg-white rounded-2xl shadow-md border border-gray-100">
                <div class="p-6 pb-4">
                    {{-- Title row --}}
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <h3 class="text-2xl font-bold text-gray-900">Completed Events</h3>
                            <span id="resultCount" class="shrink-0 text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-600">
                            </span>
                        </div>

                        <button id="clearFilters" type="button"
                                class="shrink-0 text-sm font-semibold text-purple-700 hover:text-purple-900">
                            Clear
                        </button>
                    </div>

                    {{-- Controls row --}}
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

                            {{-- Year Filter --}}
                            <select id="yearSelect"
                                    class="h-10 w-full sm:w-[150px] px-3 border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-purple-300">
                                <option value="">All Years</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" @if($selectedYear == $year) selected @endif>{{ $year }}</option>
                                @endforeach
                            </select>

                            {{-- Sort --}}
                            <select id="sortSelect"
                                    class="h-10 w-full sm:w-[190px] px-3 border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-purple-300">
                                <option value="date_desc">Sort: Latest</option>
                                <option value="date_asc">Sort: Oldest</option>
                                <option value="name_asc">Sort: Name A–Z</option>
                                <option value="name_desc">Sort: Name Z–A</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Grid --}}
                <div class="px-6 pb-6">
                    @if($completedEvents->count() > 0)
                        <div id="eventsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($completedEvents as $event)
                                @php
                                    $eventName = $event->name ?? '';
                                    $dateIso = optional($event->date)->format('Y-m-d') ?? '';
                                    $timeIso = optional($event->start_time)->format('H:i') ?? '';
                                    $dateTimeIso = ($dateIso && $timeIso) ? ($dateIso.'T'.$timeIso) : $dateIso;
                                    $yearIso = optional($event->date)->format('Y') ?? '';
                                @endphp

                                <div
                                    class="event-card"
                                    data-href="{{ route('student.event.show', $event->id) }}"
                                    data-name="{{ strtolower($eventName) }}"
                                    data-date="{{ $dateIso }}"
                                    data-datetime="{{ $dateTimeIso }}"
                                    data-year="{{ $yearIso }}"
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
                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                                    Completed
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
                                                    <span>{{ optional($event->date)->format('D, M d Y') ?? 'TBA' }}</span>
                                                </p>
                                                <p class="flex items-center gap-2">
                                                    <span class="text-gray-400">📍</span>
                                                    <span class="truncate">{{ $event->location ?? 'TBA' }}</span>
                                                </p>
                                            </div>

                                            <div class="mt-4 flex items-center justify-between gap-3">
                                                <button
                                                    data-like-event-id="{{ $event->id }}"
                                                    class="like-btn-{{ $event->id }} flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-semibold transition {{ in_array($event->id, $likedEventIds) ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                                                >
                                                    <span class="like-icon-{{ $event->id }}">{{ in_array($event->id, $likedEventIds) ? '❤️' : '🤍' }}</span>
                                                    <span class="like-count-{{ $event->id }}">{{ $event->likes()->count() }}</span>
                                                </button>
                                                <button class="px-4 py-2 bg-gray-400 text-white rounded-lg text-sm font-semibold cursor-not-allowed" disabled>
                                                    Past Event
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- No results --}}
                        <div id="noResults" class="hidden text-center py-12">
                            <p class="text-3xl mb-3">🔎</p>
                            <p class="text-gray-500 text-lg">No events match your search</p>
                            <p class="text-gray-400 text-sm mt-2">Try different keywords or clear filters.</p>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <p class="text-3xl mb-3">📭</p>
                            <p class="text-gray-500 text-lg">No completed events</p>
                            <p class="text-gray-400 text-sm mt-2">Completed events will appear here.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Make card clickable
        document.querySelectorAll('.event-card').forEach(card => {
            card.addEventListener('click', () => {
                const href = card.dataset.href;
                if (href) window.location.href = href;
            });
        });

        // Handle like button clicks without inline JS to avoid Blade parsing issues.
        document.querySelectorAll('[data-like-event-id]').forEach(button => {
            button.addEventListener('click', event => {
                event.stopPropagation();
                const eventId = Number(button.dataset.likeEventId);
                if (Number.isFinite(eventId)) {
                    toggleLike(eventId);
                }
            });
        });

        // Filters + Sort
        const searchInput = document.getElementById('eventSearch');
        const sortSelect = document.getElementById('sortSelect');
        const yearSelect = document.getElementById('yearSelect');
        const clearBtn = document.getElementById('clearFilters');

        const grid = document.getElementById('eventsGrid');
        const cards = Array.from(document.querySelectorAll('.event-card'));
        const resultCount = document.getElementById('resultCount');
        const noResults = document.getElementById('noResults');

        function normalize(v) { return (v || '').toString().trim().toLowerCase(); }

        function applyFiltersAndSort() {
            const q = normalize(searchInput?.value);
            const sort = normalize(sortSelect?.value);
            const year = yearSelect?.value;

            let visible = cards.filter(card => {
                const name = normalize(card.dataset.name);
                const cardYear = card.dataset.year;
                const matchSearch = !q || name.includes(q);
                const matchYear = !year || cardYear === year;
                const matches = matchSearch && matchYear;
                card.classList.toggle('hidden', !matches);
                return matches;
            });

            visible.sort((a, b) => {
                const aName = normalize(a.dataset.name);
                const bName = normalize(b.dataset.name);
                const aDt = a.dataset.datetime || a.dataset.date || '';
                const bDt = b.dataset.datetime || b.dataset.date || '';

                if (sort === 'name_asc') return aName.localeCompare(bName);
                if (sort === 'name_desc') return bName.localeCompare(aName);
                if (sort === 'date_asc') return aDt.localeCompare(bDt);
                return bDt.localeCompare(aDt);
            });

            if (grid) visible.forEach(v => grid.appendChild(v));

            if (resultCount) {
                const total = cards.length;
                resultCount.textContent = `${visible.length} of ${total}`;
            }
            if (noResults) noResults.classList.toggle('hidden', visible.length !== 0);
        }

        applyFiltersAndSort();

        [searchInput, sortSelect, yearSelect].forEach(el => {
            if (!el) return;
            el.addEventListener('input', applyFiltersAndSort);
            el.addEventListener('change', applyFiltersAndSort);
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                if (sortSelect) sortSelect.value = 'date_desc';
                if (yearSelect) yearSelect.value = '';
                applyFiltersAndSort();
            });
        }
    });

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
