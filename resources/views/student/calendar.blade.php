<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="{{ asset('images/uitm_logo.png') }}?v={{ filemtime(public_path('images/uitm_logo.png')) }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Calendar | Campus Event Hub</title>
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

            <a href="{{ route('student.calendar') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-purple-500 hover:bg-purple-400 transition text-sm font-medium">
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

            <!-- Header -->
            <div class="bg-gradient-to-r from-purple-600 to-purple-800 text-white rounded-2xl px-10 py-7 mb-6">
                <h2 class="text-4xl font-bold">📅 Calendar</h2>
                <p class="text-purple-100 mt-1">View all campus events</p>
            </div>

            <!-- Quick Jump + Month Nav -->
            <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-5 mb-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <!-- Jump controls -->
                    <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
                        <div class="text-sm font-semibold text-gray-700">Jump to:</div>

                        <div class="flex gap-3 flex-wrap">
                            <select id="jumpMonth" class="py-2 px-3 border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-purple-300">
                                <option value="0">January</option>
                                <option value="1">February</option>
                                <option value="2">March</option>
                                <option value="3">April</option>
                                <option value="4">May</option>
                                <option value="5">June</option>
                                <option value="6">July</option>
                                <option value="7">August</option>
                                <option value="8">September</option>
                                <option value="9">October</option>
                                <option value="10">November</option>
                                <option value="11">December</option>
                            </select>

                            <select id="jumpYear" class="py-2 px-3 border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-purple-300"></select>

                            <button id="jumpBtn" class="py-2 px-4 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition">
                                Go
                            </button>

                            <button id="todayBtn" class="py-2 px-4 border border-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition">
                                Today
                            </button>
                        </div>
                    </div>

                    <!-- Month navigation -->
                    <div class="flex items-center justify-between gap-3">
                        <button id="prevMonth" class="px-4 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                            ← Previous
                        </button>
                        <h3 class="text-2xl font-bold text-gray-900 text-center min-w-[170px]" id="monthTitle"></h3>
                        <button id="nextMonth" class="px-4 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                            Next →
                        </button>
                    </div>
                </div>

                <div class="mt-3 text-xs text-gray-500">
                    Tip: Use <span class="font-semibold">Jump to</span> to quickly go to any month/year (e.g., 2005) without clicking next many times.
                </div>
            </div>

            <!-- Month View -->
            <div id="monthView" class="space-y-6">

                <!-- Calendar Grid -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
                    <!-- Weekday Headers -->
                    <div class="grid grid-cols-7 bg-gradient-to-r from-purple-100 to-purple-50 border-b border-gray-200">
                        <div class="p-4 text-center font-semibold text-gray-700">Sun</div>
                        <div class="p-4 text-center font-semibold text-gray-700">Mon</div>
                        <div class="p-4 text-center font-semibold text-gray-700">Tue</div>
                        <div class="p-4 text-center font-semibold text-gray-700">Wed</div>
                        <div class="p-4 text-center font-semibold text-gray-700">Thu</div>
                        <div class="p-4 text-center font-semibold text-gray-700">Fri</div>
                        <div class="p-4 text-center font-semibold text-gray-700">Sat</div>
                    </div>

                    <!-- Calendar Days -->
                    <div id="calendarGrid" class="grid grid-cols-7 divide-x divide-y divide-gray-200"></div>
                </div>

                <!-- Date Events Popup -->
                <div id="dateEventsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-96 overflow-y-auto">
                        <div class="sticky top-0 bg-gradient-to-r from-purple-600 to-purple-800 text-white p-6 flex items-center justify-between">
                            <h4 id="modalDateTitle" class="text-xl font-bold"></h4>
                            <button id="closeModal" class="text-white hover:text-purple-200 text-2xl font-bold">×</button>
                        </div>
                        <div id="modalEventsList" class="p-6 space-y-4"></div>
                    </div>
                </div>

            </div>

        </div>
    </main>
</div>

<script>
    // Parse events from PHP
    const eventsData = @json($events);

    // Robust date normalizer -> YYYY-MM-DD (handles "2026-01-05", "2026-01-05T00:00:00Z", etc.)
    function toYMD(d) {
        const dt = new Date(d);
        if (!isNaN(dt)) {
            const y = dt.getFullYear();
            const m = String(dt.getMonth() + 1).padStart(2, '0');
            const day = String(dt.getDate()).padStart(2, '0');
            return `${y}-${m}-${day}`;
        }
        // fallback if date can't parse
        return String(d).split('T')[0];
    }

    // Build map: date -> events[]
    const eventsByDate = {};
    eventsData.forEach(ev => {
        const key = toYMD(ev.date);
        (eventsByDate[key] ||= []).push(ev);
    });

    // Determine min/max year from data for Jump Year dropdown
    const today = new Date();
    const yearsFromData = eventsData
        .map(e => new Date(e.date))
        .filter(d => !isNaN(d))
        .map(d => d.getFullYear());

    const minYear = yearsFromData.length ? Math.min(...yearsFromData) : today.getFullYear() - 5;
    const maxYear = yearsFromData.length ? Math.max(...yearsFromData) : today.getFullYear() + 5;

    // Current calendar date pointer
    let currentDate = new Date();

    // Elements
    const monthTitle = document.getElementById('monthTitle');
    const calendarGrid = document.getElementById('calendarGrid');
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');

    const jumpMonth = document.getElementById('jumpMonth');
    const jumpYear = document.getElementById('jumpYear');
    const jumpBtn = document.getElementById('jumpBtn');
    const todayBtn = document.getElementById('todayBtn');

    // Month names
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                        'July', 'August', 'September', 'October', 'November', 'December'];

    // Init Jump Year dropdown
    (function initYearDropdown() {
        // If you want to allow jump outside data range (e.g., 2005 even if no events),
        // uncomment below and set a wider range:
        // const min = Math.min(minYear, 2000);
        // const max = Math.max(maxYear, 2035);

        const min = minYear;
        const max = maxYear;

        jumpYear.innerHTML = '';
        for (let y = min; y <= max; y++) {
            const opt = document.createElement('option');
            opt.value = String(y);
            opt.textContent = String(y);
            jumpYear.appendChild(opt);
        }
    })();

    // Sync Jump controls to currentDate
    function syncJumpControls() {
        jumpMonth.value = String(currentDate.getMonth());
        // if year is outside dropdown (possible if you set strict min/max), clamp
        const y = currentDate.getFullYear();
        const years = Array.from(jumpYear.options).map(o => parseInt(o.value, 10));
        if (years.length && (y < years[0] || y > years[years.length - 1])) {
            // do nothing / or clamp
            jumpYear.value = String(Math.max(years[0], Math.min(y, years[years.length - 1])));
        } else {
            jumpYear.value = String(y);
        }
    }

    // Render Calendar
    function renderCalendar() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();

        monthTitle.textContent = `${monthNames[month]} ${year}`;
        syncJumpControls();

        // First weekday and days count
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        calendarGrid.innerHTML = '';

        // Empty cells before month starts (make borders consistent)
        for (let i = 0; i < firstDay; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.className = 'p-4 bg-gray-50 min-h-24 border-t border-gray-200';
            calendarGrid.appendChild(emptyCell);
        }

        // Day cells
        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const dayEvents = eventsByDate[dateStr] || [];

            const isToday =
                year === today.getFullYear() &&
                month === today.getMonth() &&
                day === today.getDate();

            const dayCell = document.createElement('div');
            dayCell.className =
                'p-4 min-h-24 bg-white hover:bg-purple-50 transition cursor-pointer border-t border-gray-200 relative';

            if (isToday) {
                dayCell.classList.add('ring-2', 'ring-purple-300');
            }

            let cellHTML = `
                <div class="flex items-start justify-between mb-2">
                    <div class="font-semibold text-gray-900">${day}</div>
                    ${isToday ? '<span class="text-[10px] font-bold text-purple-700 bg-purple-100 px-2 py-0.5 rounded-full">Today</span>' : ''}
                </div>
            `;

            if (dayEvents.length > 0) {
                dayEvents.slice(0, 2).forEach(event => {
                    const statusBg = {
                        'Upcoming': 'bg-blue-100',
                        'Currently Running': 'bg-green-100',
                        'Completed': 'bg-gray-100'
                    }[event.status] || 'bg-gray-100';

                    cellHTML += `
                        <div class="text-xs font-semibold ${statusBg} text-gray-700 px-2 py-1 rounded mb-1 truncate">
                            ${event.name}
                        </div>
                    `;
                });

                if (dayEvents.length > 2) {
                    cellHTML += `<div class="text-xs text-purple-600 font-semibold">+${dayEvents.length - 2} more</div>`;
                }
            } else {
                // subtle placeholder so cell doesn't look empty
                cellHTML += `<div class="text-xs text-gray-300 mt-2">No events</div>`;
            }

            // Make ALL days clickable (even empty)
            dayCell.addEventListener('click', () => showDateModal(dateStr, dayEvents));

            dayCell.innerHTML = cellHTML;
            calendarGrid.appendChild(dayCell);
        }
    }

    // Show Date Events Modal
    function showDateModal(dateStr, events) {
        const modal = document.getElementById('dateEventsModal');
        const dateTitle = document.getElementById('modalDateTitle');
        const eventsList = document.getElementById('modalEventsList');

        const date = new Date(dateStr);
        dateTitle.textContent = `${monthNames[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`;

        eventsList.innerHTML = '';

        if (!events || events.length === 0) {
            eventsList.innerHTML = `
                <div class="p-4 bg-gray-50 rounded-lg text-center">
                    <p class="text-gray-700 font-semibold">No events on this date</p>
                    <p class="text-xs text-gray-500 mt-1">Try another day or use Jump to change month/year.</p>
                </div>
            `;
        } else {
            // sort by start_time if present
            const sorted = [...events].sort((a, b) => String(a.start_time || '').localeCompare(String(b.start_time || '')));

            sorted.forEach(event => {
                const statusColors = {
                    'Upcoming': 'bg-blue-100 text-blue-700',
                    'Currently Running': 'bg-green-100 text-green-700',
                    'Completed': 'bg-gray-100 text-gray-700'
                };

                const eventHTML = `
                    <a href="/student/event/${event.id}" class="block p-3 bg-gray-50 rounded-lg hover:bg-purple-100 transition">
                        <div class="flex items-start justify-between gap-2 mb-1">
                            <h5 class="font-semibold text-gray-900 truncate">${event.name}</h5>
                            <span class="text-xs font-semibold px-2 py-1 rounded-full whitespace-nowrap ${statusColors[event.status] || 'bg-gray-100 text-gray-700'}">
                                ${event.status || 'Unknown'}
                            </span>
                        </div>
                        <p class="text-xs text-gray-600">🕐 ${formatTime(event.start_time)}</p>
                        <p class="text-xs text-gray-600">📍 ${event.location || 'TBA'}</p>
                    </a>
                `;
                eventsList.innerHTML += eventHTML;
            });
        }

        modal.classList.remove('hidden');
    }

    // Format Time Helper (handles "HH:mm:ss", "HH:mm", null)
    function formatTime(timeStr) {
        if (!timeStr) return 'TBA';
        const parts = String(timeStr).split(':');
        if (parts.length < 2) return String(timeStr);

        const hours = parseInt(parts[0], 10);
        const minutes = parts[1];
        if (isNaN(hours)) return String(timeStr);

        const ampm = hours >= 12 ? 'PM' : 'AM';
        const displayHour = hours % 12 || 12;
        return `${String(displayHour).padStart(2, '0')}:${minutes} ${ampm}`;
    }

    // Modal close
    document.getElementById('closeModal').addEventListener('click', () => {
        document.getElementById('dateEventsModal').classList.add('hidden');
    });
    document.getElementById('dateEventsModal').addEventListener('click', (e) => {
        if (e.target.id === 'dateEventsModal') {
            document.getElementById('dateEventsModal').classList.add('hidden');
        }
    });

    // Month navigation
    prevMonthBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    });
    nextMonthBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    });

    // Jump to month/year
    jumpBtn.addEventListener('click', () => {
        const y = parseInt(jumpYear.value, 10);
        const m = parseInt(jumpMonth.value, 10);
        if (isNaN(y) || isNaN(m)) return;
        currentDate = new Date(y, m, 1);
        renderCalendar();
    });

    // Today
    todayBtn.addEventListener('click', () => {
        currentDate = new Date();
        renderCalendar();
    });

    // Initial render (month view only; list view removed)
    renderCalendar();
</script>

</body>
</html>
