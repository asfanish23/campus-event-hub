<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $club->name }} | Campus Event Hub</title>
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
                <a href="{{ route('super-admin.manage-events') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>📋</span>
                    <span>Manage Events</span>
                </a>
                <a href="{{ route('super-admin.manage-clubs') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-purple-500 hover:bg-purple-400 transition text-sm font-medium">
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
                <h2 class="text-2xl font-bold text-gray-800">{{ $club->name }}</h2>
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

            <!-- Club Details -->
            <div class="p-8">
                <!-- Background Photo -->
                <div class="mb-8 -mx-8">
                    @if($club->background_photo)
                        @php
                            $posH = 50 + (($club->background_position_h ?? 0) / 2);
                            $posV = 50 + (($club->background_position_v ?? 0) / 2);
                        @endphp
                        <div style="height: 256px; background-image: url('{{ asset('storage/' . $club->background_photo) }}'); background-position: {{ $posH }}% {{ $posV }}%; background-size: cover; background-repeat: no-repeat;"></div>
                    @else
                        <div class="w-full bg-gradient-to-r from-purple-500 to-purple-700 flex items-center justify-center text-5xl opacity-50" style="height: 256px;">
                            📸
                        </div>
                    @endif
                </div>

                <div class="max-w-4xl bg-white rounded-lg shadow p-8">
                    <!-- Club Header Section -->
                    <div class="mb-8 pb-8 border-b border-gray-200 flex items-start gap-6">
                        <div class="flex-shrink-0">
                            @if($club->profile_photo)
                                <img src="{{ asset('storage/' . $club->profile_photo) }}" alt="{{ $club->name }}" class="w-32 h-32 rounded-lg object-cover border-2 border-gray-300">
                            @else
                                <div class="w-32 h-32 bg-gradient-to-br from-purple-500 to-purple-700 rounded-lg flex items-center justify-center text-5xl border-2 border-gray-300">
                                    🏢
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-4 mb-4">
                                <h3 class="text-2xl font-bold text-gray-800">{{ $club->name }}</h3>
                                <span class="px-4 py-1 bg-purple-100 text-purple-800 rounded-full text-sm font-semibold">{{ $club->category }}</span>
                            </div>
                            <div class="space-y-2">
                                <p class="text-sm text-gray-600">
                                    <strong>Total Members:</strong> {{ $club->total_members ?? 0 }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Club Information Section -->
                    <div class="mb-8 pb-8 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">About Club</h3>
                        <p class="text-gray-700 whitespace-pre-line leading-relaxed">{{ $club->description ?? 'No description available' }}</p>
                    </div>

                    <!-- Club Details Section -->
                    <div class="mb-8 pb-8 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800 mb-6">Club Details</h3>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                                <p class="text-gray-800">{{ $club->email ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Total Members</label>
                                <p class="text-gray-800">{{ $club->total_members ?? 0 }}</p>
                            </div>
                            @if($club->founded_date)
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">📅 Founded</label>
                                <p class="text-gray-800">{{ $club->founded_date->format('F d, Y') }}</p>
                            </div>
                            @endif
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                                <p class="text-gray-800">{{ $club->category }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- President Information Section -->
                    @if($club->president_name || $club->president_contact)
                    <div class="mb-8 pb-8 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800 mb-6">President Information</h3>
                        <div class="grid grid-cols-2 gap-6">
                            @if($club->president_name)
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">President Name</label>
                                <p class="text-gray-800">{{ $club->president_name }}</p>
                            </div>
                            @endif
                            @if($club->president_contact)
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Contact Number</label>
                                <p class="text-gray-800">{{ $club->president_contact }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Social Media Section -->
                    @if($club->facebook_url || $club->instagram_url || $club->twitter_url)
                    <div class="mb-8">
                        <h3 class="text-lg font-bold text-gray-800 mb-6">Social Media Links</h3>
                        <div class="space-y-3">
                            @if($club->facebook_url)
                            <div class="flex items-center gap-3">
                                <span class="text-lg">📘</span>
                                <a href="{{ $club->facebook_url }}" target="_blank" class="text-blue-600 hover:text-blue-800 underline">{{ $club->facebook_url }}</a>
                            </div>
                            @endif
                            @if($club->instagram_url)
                            <div class="flex items-center gap-3">
                                <span class="text-lg">📷</span>
                                <a href="{{ $club->instagram_url }}" target="_blank" class="text-blue-600 hover:text-blue-800 underline">{{ $club->instagram_url }}</a>
                            </div>
                            @endif
                            @if($club->twitter_url)
                            <div class="flex items-center gap-3">
                                <span class="text-lg">𝕏</span>
                                <a href="{{ $club->twitter_url }}" target="_blank" class="text-blue-600 hover:text-blue-800 underline">{{ $club->twitter_url }}</a>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex gap-4">
                        <a href="{{ route('super-admin.clubs.edit', $club) }}" class="px-8 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                            ✏️ Edit Club
                        </a>
                        <a href="{{ route('super-admin.manage-clubs') }}" class="px-8 py-3 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-semibold">
                            ← Back to Clubs
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
