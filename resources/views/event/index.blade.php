<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management | Campus Event Hub</title>
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
                <h2 class="text-2xl font-bold text-gray-800">Event Management</h2>
                <div class="flex items-center gap-6">
                    <button class="text-gray-600 text-xl">🔔</button>
                    <a href="{{ route('event.create') }}" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                        ✨ Create Event
                    </a>
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

            <!-- Events Content -->
            <div class="p-8">
                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Header with description -->
                <p class="text-gray-600 mb-6">Create and manage your club events</p>

                <!-- Filters -->
                <div class="mb-6 bg-white rounded-lg shadow p-6">
                    <form method="GET" action="{{ route('event.index') }}" class="flex gap-4 items-end">
                        <div class="flex-1">
                            <input type="text" name="search" placeholder="Search events by name or category..." value="{{ request('search') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                        </div>
                        <div class="w-40">
                            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                <option value="">All Status</option>
                                <option value="Upcoming" {{ request('status') === 'Upcoming' ? 'selected' : '' }}>Upcoming</option>
                                <option value="Currently Running" {{ request('status') === 'Currently Running' ? 'selected' : '' }}>Currently Running</option>
                                <option value="Completed" {{ request('status') === 'Completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>
                        <div class="w-40">
                            <select name="year" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                <option value="">All Years</option>
                                <option value="2024" {{ request('year') === '2024' ? 'selected' : '' }}>2024</option>
                                <option value="2025" {{ request('year') === '2025' ? 'selected' : '' }}>2025</option>
                                <option value="2026" {{ request('year') === '2026' ? 'selected' : '' }}>2026</option>
                            </select>
                        </div>
                        <div class="w-40">
                            <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">Filter</button>
                            <a href="{{ route('event.index') }}" class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-semibold">Reset</a>
                        </div>
                    </form>
                </div>

                <!-- Events Count -->
                <p class="text-gray-600 mb-4">Showing {{ count($events) }} of {{ count($events) }} events</p>

                <!-- Events Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-purple-600 text-white">
                                <th class="px-6 py-4 text-left font-semibold">Event Name</th>
                                <th class="px-6 py-4 text-left font-semibold">Date</th>
                                <th class="px-6 py-4 text-left font-semibold">Category</th>
                                <th class="px-6 py-4 text-left font-semibold">Status</th>
                                <th class="px-6 py-4 text-left font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($events as $event)
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 font-semibold text-gray-800">{{ $event->name }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $event->date->format('Y-m-d') }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 bg-blue-100 text-blue-600 rounded-full text-xs font-semibold">{{ $event->category }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $actualStatus = $event->getComputedStatus();
                                            $displayStatus = match ($actualStatus) {
                                                'ongoing' => 'Currently Running',
                                                default => ucfirst($actualStatus),
                                            };
                                            $statusColor = match ($actualStatus) {
                                                'upcoming' => 'blue',
                                                'ongoing' => 'green',
                                                default => 'gray',
                                            };
                                        @endphp
                                        <span class="px-3 py-1 bg-{{ $statusColor }}-100 text-{{ $statusColor }}-600 rounded-full text-xs font-semibold">
                                            {{ $displayStatus }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex gap-2 items-center">
                                            <a href="{{ route('event.show', $event) }}" class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition" title="View">👁️</a>
                                            <a href="{{ route('event.edit', $event) }}" class="p-2 text-purple-600 hover:bg-purple-100 rounded-lg transition" title="Edit">✏️</a>
                                            <a href="{{ route('event.attendance', $event) }}" class="p-2 text-orange-600 hover:bg-orange-100 rounded-lg transition" title="Attendance">👥</a>
                                            <a href="{{ route('event.reviews', $event) }}" class="p-2 text-green-600 hover:bg-green-100 rounded-lg transition" title="Reviews">⭐</a>
                                            <form method="POST" action="{{ route('event.destroy', $event) }}" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition" title="Delete">🗑️</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        No events found. <a href="{{ route('event.create') }}" class="text-purple-600 hover:underline">Create one now!</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
