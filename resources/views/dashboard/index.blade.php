<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Campus Event Hub</title>
    @vite('resources/css/app.css')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-48 bg-gradient-to-b from-purple-600 to-purple-800 text-white overflow-y-auto flex flex-col">
            <div class="p-6 border-b border-purple-500">
                <h1 class="text-xl font-bold">Campus Event Hub</h1>
                <p class="text-sm text-purple-200">{{ $club->name ?? 'Club Admin Panel' }}</p>
            </div>

            <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-purple-500 hover:bg-purple-400 transition text-sm font-medium">
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
                <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>
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

            <!-- Dashboard Content -->
            <div class="p-8">
                <p class="text-gray-600 mb-6">Welcome back! Here's an overview of your club.</p>

                {{-- Instagram Activity Notifications --}}
                @if(isset($instagramNotifications) && count($instagramNotifications) > 0)
                <div class="mb-8 bg-gradient-to-r from-pink-50 to-purple-50 rounded-lg shadow p-6 border border-pink-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-800">📷 Instagram Activity</h3>
                        <span class="text-xs px-2 py-1 bg-pink-200 text-pink-700 rounded-full font-semibold">
                            {{ count($instagramNotifications) }} {{ count($instagramNotifications) === 1 ? 'update' : 'updates' }}
                        </span>
                    </div>

                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        @foreach($instagramNotifications as $notification)
                        <div class="bg-white rounded-lg p-3 border-l-4 border-pink-400 hover:shadow-md transition">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-lg">{{ $notification['activity_icon'] ?? '📱' }}</span>
                                        <p class="font-semibold text-sm text-gray-800">
                                            {{ $notification['event']['name'] ?? 'Event' }}
                                        </p>
                                    </div>
                                    <p class="text-gray-600 text-sm">{{ $notification['message'] }}</p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ $notification['created_at'] }}
                                    </p>
                                </div>
                                @if(!$notification['read'])
                                <div class="w-2 h-2 bg-pink-500 rounded-full mt-1"></div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-4 text-center">
                        <a href="{{ route('instagram.index') }}" class="text-sm text-pink-600 hover:text-pink-700 font-semibold">
                            View all Instagram activity →
                        </a>
                    </div>
                </div>
                @endif

                <!-- Stats Cards -->
                <div class="grid grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-gray-600 text-sm">Total Events</p>
                                <p class="text-4xl font-bold text-gray-800 mt-2">{{ $totalEvents }}</p>
                                <p class="text-xs text-green-600 mt-2">All events</p>
                            </div>
                            <span class="text-3xl">📅</span>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-gray-600 text-sm">Upcoming Events</p>
                                <p class="text-4xl font-bold text-gray-800 mt-2">{{ $upcomingEvents }}</p>
                                <p class="text-xs text-blue-600 mt-2">Coming soon</p>
                            </div>
                            <span class="text-3xl">🎯</span>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-gray-600 text-sm">Avg Attendance</p>
                                <p class="text-4xl font-bold text-gray-800 mt-2">{{ $avgAttendance }}%</p>
                                <p class="text-xs text-green-600 mt-2">Performance</p>
                            </div>
                            <span class="text-3xl">👥</span>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-gray-600 text-sm">Merch Sales</p>
                                <p class="text-4xl font-bold text-gray-800 mt-2">RM {{ $merchSales }}</p>
                                <p class="text-xs text-orange-600 mt-2">Revenue</p>
                            </div>
                            <span class="text-3xl">🛍️</span>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-2 gap-6 mb-8">
                    <!-- Attendance Trend Chart -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Attendance Trend</h3>
                        <canvas id="attendanceChart"></canvas>
                    </div>

                    <!-- Event Performance Chart -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Event Performance</h3>
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>

                <!-- Recent Feedback & Social Media Row -->
                <div class="grid grid-cols-2 gap-6">
                    <!-- Recent Feedback -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Recent Feedback</h3>
                        <div class="space-y-4">
                            <div>
                                <p class="font-semibold text-gray-800">Sarah Ahmad</p>
                                <p class="text-yellow-400">⭐⭐⭐⭐⭐</p>
                                <p class="text-sm text-gray-600">Tech Talk 2024</p>
                                <p class="text-sm text-gray-700">Amazing event! Very informative.</p>
                            </div>
                            <div class="border-t pt-4">
                                <p class="font-semibold text-gray-800">Mike Chen</p>
                                <p class="text-yellow-400">⭐⭐⭐⭐</p>
                                <p class="text-sm text-gray-600">AI Workshop</p>
                                <p class="text-sm text-gray-700">Great content, need more time for Q&A.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Social Media</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between pb-4 border-b">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">f</span>
                                    <div>
                                        <p class="font-semibold text-gray-800">Facebook</p>
                                        <p class="text-xs text-gray-500">Connected</p>
                                    </div>
                                </div>
                                <span class="text-green-500">🟢</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">📷</span>
                                    <div>
                                        <p class="font-semibold text-gray-800">Instagram</p>
                                        <p class="text-xs text-gray-500">Connected</p>
                                    </div>
                                </div>
                                <span class="text-green-500">🟢</span>
                            </div>
                            <div class="pt-4 border-t">
                                <p class="text-sm font-semibold text-gray-800">Last Post</p>
                                <p class="text-purple-600 text-sm">Tech Workshop 2024</p>
                                <p class="text-xs text-gray-500">2 hours ago</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Attendance Trend Chart
        const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(attendanceCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Attendance Rate (%)',
                    data: [65, 70, 75, 80, 78, 82],
                    borderColor: '#9333ea',
                    backgroundColor: 'rgba(147, 51, 234, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#9333ea',
                    pointRadius: 5,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: { callback: function(value) { return value + '%'; } }
                    }
                }
            }
        });

        // Event Performance Chart
        const performanceCtx = document.getElementById('performanceChart').getContext('2d');
        new Chart(performanceCtx, {
            type: 'bar',
            data: {
                labels: ['Tech Talk', 'Workshop', 'Hackathon', 'Seminar'],
                datasets: [{
                    label: 'Attendance',
                    data: [120, 80, 140, 90],
                    backgroundColor: '#9333ea',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>
