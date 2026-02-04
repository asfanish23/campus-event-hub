<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events | Campus Event Hub</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-48 bg-gradient-to-b from-purple-600 to-purple-800 text-white overflow-y-auto flex flex-col">
            <div class="p-6 border-b border-purple-500">
                <h1 class="text-xl font-bold">Campus Event Hub</h1>
                <p class="text-sm text-purple-200">Super Admin Panel</p>
            </div>

            <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto">
                <a href="{{ route('super-admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>📊</span>
                    <span>Dashboard</span>
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-4 text-xs text-purple-300 uppercase font-semibold tracking-wide">Management</p>
                </div>
                <a href="{{ route('super-admin.manage-events') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-purple-500 hover:bg-purple-400 transition text-sm font-medium">
                    <span>📋</span>
                    <span>Manage Events</span>
                </a>
                <a href="{{ route('super-admin.manage-clubs') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>👥</span>
                    <span>Manage Clubs</span>
                </a>
                <a href="{{ route('super-admin.manage-users') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>👤</span>
                    <span>Manage Users</span>
                </a>
                <a href="{{ route('super-admin.manage-reviews') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>⭐</span>
                    <span>Manage Reviews</span>
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-4 text-xs text-purple-300 uppercase font-semibold tracking-wide">Configuration</p>
                </div>
                <a href="{{ route('super-admin.system-settings') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>⚙️</span>
                    <span>System Settings</span>
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
                <h2 class="text-2xl font-bold text-gray-800">Manage Events</h2>
                <div class="flex items-center gap-6">
                    <button class="text-gray-600 text-xl">🔔</button>
                    <div class="flex items-center gap-3 pl-6 border-l border-gray-300">
                        <div class="text-right">
                            <p class="font-semibold text-gray-800">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500">Super Admin</p>
                        </div>
                        <div class="w-10 h-10 bg-purple-600 text-white rounded-full flex items-center justify-center font-bold">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-8">
                @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
                @endif

                <!-- Create Event Button -->
                <div class="mb-6">
                    <a href="{{ route('super-admin.events.create') }}" class="inline-block px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                        ➕ Create New Event
                    </a>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow mb-6 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Filters</h3>
                    <form method="GET" action="{{ route('super-admin.manage-events') }}" class="flex gap-4 flex-wrap">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search by Name</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search events..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Club</label>
                            <select name="club" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                                <option value="">All Clubs</option>
                                @foreach($clubs as $club)
                                <option value="{{ $club->id }}" {{ request('club') == $club->id ? 'selected' : '' }}>{{ $club->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Status</label>
                            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                                <option value="">All Status</option>
                                @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-2 items-end">
                            <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-medium">Filter</button>
                            <a href="{{ route('super-admin.manage-events') }}" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition font-medium">Reset</a>
                        </div>
                    </form>
                </div>

                <!-- Events Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800">All Events</h3>
                        <p class="text-sm text-gray-600">Total: {{ $events->total() }} events</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Event Name</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Club</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Location</th>
                                    <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">Attendees</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                                    <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">QR</th>
                                    <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($events as $event)
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ $event->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ optional($event->club)->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ \Carbon\Carbon::parse($event->date)->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $event->location }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">{{ $event->attendances->count() ?? 0 }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        @php
                                            $today = now()->startOfDay();
                                            $eventDate = \Carbon\Carbon::parse($event->date)->startOfDay();
                                            
                                            if ($eventDate < $today) {
                                                $actualStatus = 'Completed';
                                                $statusColor = 'gray';
                                            } elseif ($eventDate->isSameDay($today)) {
                                                $actualStatus = 'Currently Running';
                                                $statusColor = 'green';
                                            } else {
                                                $actualStatus = 'Upcoming';
                                                $statusColor = 'yellow';
                                            }
                                        @endphp
                                        <span class="px-3 py-1 bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800 rounded-full text-xs font-semibold">{{ $actualStatus }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <form method="POST" action="{{ route('super-admin.events.toggle-qr', $event) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-2 rounded-lg text-xs font-semibold transition {{ $event->qr_active ? 'bg-green-400 text-white shadow-lg shadow-green-500/50 animate-pulse' : 'bg-gray-300 text-gray-700 hover:bg-gray-400' }}">
                                                {{ $event->qr_active ? '✓ Active' : 'Inactive' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center gap-2 flex-wrap">
                                            <a href="{{ route('super-admin.events.show', $event) }}" class="px-3 py-1 bg-blue-600 text-white rounded text-xs font-medium hover:bg-blue-700">View</a>
                                            <a href="{{ route('super-admin.events.edit', $event) }}" class="px-3 py-1 bg-orange-600 text-white rounded text-xs font-medium hover:bg-orange-700">Edit</a>
                                            <form method="POST" action="{{ route('super-admin.events.delete', $event) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded text-xs font-medium hover:bg-red-700">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">No events found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="p-6 border-t border-gray-200">
                        {{ $events->links() }}
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
