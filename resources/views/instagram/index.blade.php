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
                            <h1 class="text-3xl font-bold text-gray-900">📷 Instagram Management</h1>
                            <p class="text-gray-600 mt-2">Post and schedule your events to Instagram</p>
                        </div>
                        <a href="{{ route('instagram.settings') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center gap-2">
                            ⚙️ Settings
                        </a>
                    </div>
                </div>

                <!-- Status Alert -->
                @if(!$hasCredentials)
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                ⚠️
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
                                ✅
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-800">
                                    Instagram credentials configured and ready to post!
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Messages -->
                @if($message = session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                        ✅ {{ $message }}
                    </div>
                @endif

                @if($message = session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                        ❌ {{ $message }}
                    </div>
                @endif

                <!-- Search, Filter & Sort Bar -->
                <div class="bg-white rounded-lg shadow p-6 mb-8">
                    <form method="GET" action="{{ route('instagram.index') }}" class="space-y-4">
                        <!-- Search Bar -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">🔍 Search</label>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Search by event name or location..." 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                        </div>

                        <!-- Filter Row -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Status Filter -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                    <option value="">All Status</option>
                                    <option value="Upcoming" {{ request('status') === 'Upcoming' ? 'selected' : '' }}>Upcoming</option>
                                    <option value="Currently Running" {{ request('status') === 'Currently Running' ? 'selected' : '' }}>Currently Running</option>
                                    <option value="Completed" {{ request('status') === 'Completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                            </div>

                            <!-- Category Filter -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                                <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Instagram Status Filter -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Instagram Status</label>
                                <select name="instagram_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                    <option value="">All Posts</option>
                                    <option value="posted" {{ request('instagram_status') === 'posted' ? 'selected' : '' }}>Posted</option>
                                    <option value="not_posted" {{ request('instagram_status') === 'not_posted' ? 'selected' : '' }}>Not Posted</option>
                                    <option value="scheduled" {{ request('instagram_status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                </select>
                            </div>

                            <!-- Sort By -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Sort By</label>
                                <select name="sort_by" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                    <option value="date_desc" {{ request('sort_by', 'date_desc') === 'date_desc' ? 'selected' : '' }}>📅 Date (Newest)</option>
                                    <option value="date_asc" {{ request('sort_by') === 'date_asc' ? 'selected' : '' }}>📅 Date (Oldest)</option>
                                    <option value="name_asc" {{ request('sort_by') === 'name_asc' ? 'selected' : '' }}>🔤 Name (A-Z)</option>
                                    <option value="name_desc" {{ request('sort_by') === 'name_desc' ? 'selected' : '' }}>🔤 Name (Z-A)</option>
                                    <option value="newest" {{ request('sort_by') === 'newest' ? 'selected' : '' }}>⏰ Created (Newest)</option>
                                    <option value="oldest" {{ request('sort_by') === 'oldest' ? 'selected' : '' }}>⏰ Created (Oldest)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                                🔎 Apply Filters
                            </button>
                            <a href="{{ route('instagram.index') }}" class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-semibold">
                                ↻ Clear
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Events Grid -->
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900 mb-6">Your Events ({{ $events->count() }})</h2>

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
                                            <span class="text-gray-400 text-4xl">🖼️</span>
                                        </div>
                                    @endif

                                    <!-- Event Details -->
                                    <div class="p-4">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $event->name }}</h3>
                                        
                                        <div class="space-y-2 text-sm text-gray-600 mb-4">
                                            <p>📅 {{ $event->date->format('M d, Y') }}</p>
                                            <p>📍 {{ $event->location }}</p>
                                            <p>🏷️ {{ $event->category }}</p>
                                        </div>

                                        <!-- Status Badges -->
                                        <div class="flex flex-wrap gap-2 mb-4">
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

                                            @if($event->isPostedToInstagram())
                                                <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                                    📤 Posted
                                                </span>
                                            @elseif($event->instagram_auto_post && $event->instagram_scheduled_at && !$event->instagram_scheduled_posted)
                                                <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                                    ⏱️ Scheduled
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Scheduled Info -->
                                        @if($event->instagram_scheduled_at && !$event->instagram_scheduled_posted)
                                            <div class="bg-yellow-50 border border-yellow-200 rounded p-2 mb-4 text-xs">
                                                <p class="font-semibold text-yellow-800">⏱️ Scheduled for:</p>
                                                <p class="text-yellow-700">{{ $event->instagram_scheduled_at->format('M d, Y H:i') }}</p>
                                            </div>
                                        @endif

                                        <!-- Action Buttons -->
                                        <div class="space-y-2">
                                            @if($event->event_image && $hasCredentials)
                                                @if(!$event->isPostedToInstagram() && !$event->instagram_auto_post)
                                                    <!-- Post Now Button -->
                                                    <form action="{{ route('instagram.post-event', $event) }}" method="POST" class="w-full">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="w-full bg-gradient-to-r from-pink-500 via-red-500 to-yellow-500 hover:from-pink-600 hover:via-red-600 hover:to-yellow-600 text-white font-semibold py-2 px-4 rounded-lg transition">
                                                            📤 Post Now
                                                        </button>
                                                    </form>

                                                    <!-- Schedule Button (Modal Trigger) -->
                                                    <button onclick="openScheduleModal({{ $event->id }})" 
                                                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                                                            📅 Schedule Post
                                                    </button>
                                                @elseif($event->instagram_auto_post && $event->instagram_scheduled_at && !$event->instagram_scheduled_posted)
                                                    <!-- Cancel Scheduled Post Button -->
                                                    <form action="{{ route('instagram.cancel-scheduled', $event) }}" method="POST" class="w-full">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition"
                                                                onclick="return confirm('Cancel scheduled post?')">
                                                            ❌ Cancel Schedule
                                                        </button>
                                                    </form>
                                                @else
                                                    <!-- Repost Options for Already Posted -->
                                                    <div class="space-y-2">
                                                        <form action="{{ route('instagram.repost-now', $event) }}" method="POST" class="w-full">
                                                            @csrf
                                                            <button type="submit" 
                                                                    class="w-full bg-gradient-to-r from-rose-600 to-orange-600 hover:from-rose-700 hover:to-orange-700 text-white font-semibold py-2 px-4 rounded-lg transition shadow-md"
                                                                    onclick="return confirm('Repost to Instagram now?')">
                                                                🔄 Repost Now
                                                            </button>
                                                        </form>
                                                        <button onclick="openRepostModal({{ $event->id }})" 
                                                                class="w-full bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition shadow-md">
                                                                📅 Schedule Repost
                                                        </button>
                                                    </div>
                                                @endif
                                            @elseif(!$event->event_image)
                                                <button disabled class="w-full bg-gray-300 text-gray-600 font-semibold py-2 px-4 rounded-lg cursor-not-allowed">
                                                    🖼️ No Image
                                                </button>
                                            @else
                                                <button disabled class="w-full bg-gray-300 text-gray-600 font-semibold py-2 px-4 rounded-lg cursor-not-allowed">
                                                    ⚙️ Configure First
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-lg p-8 text-center">
                            <span class="text-gray-400 text-5xl mb-4 block">📭</span>
                            <p class="text-gray-600 text-lg">No events found</p>
                            <a href="{{ route('event.create') }}" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                                ➕ Create First Event
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    <!-- Schedule Modal -->
    <div id="scheduleModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">📅 Schedule Instagram Post</h2>
            
            <form id="scheduleForm" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Select Date & Time</label>
                    <input type="datetime-local" name="instagram_scheduled_at" required
                           min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-2">⚠️ Minimum 5 minutes in the future</p>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded p-3">
                    <p class="text-sm text-blue-800">
                        💡 <strong>Tip:</strong> Schedule your post when your audience is most active. The system will post automatically at the scheduled time.
                    </p>
                </div>

                <div class="flex gap-2 pt-4">
                    <button type="submit" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                        ✅ Schedule
                    </button>
                    <button type="button" onclick="closeScheduleModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-lg transition">
                        ❌ Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Repost Modal -->
    <div id="repostModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">🔄 Schedule Repost</h2>
            
            <form id="repostForm" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Select Date & Time</label>
                    <input type="datetime-local" name="instagram_repost_at" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-2">⚠️ Minimum 5 minutes in the future</p>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded p-3">
                    <p class="text-sm text-blue-800">
                        💡 <strong>Tip:</strong> Reposting helps increase engagement. Schedule at peak hours for maximum reach.
                    </p>
                </div>

                <div class="flex gap-2 pt-4">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                        ✅ Schedule Repost
                    </button>
                    <button type="button" onclick="closeRepostModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-lg transition">
                        ❌ Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openScheduleModal(eventId) {
            const modal = document.getElementById('scheduleModal');
            const form = document.getElementById('scheduleForm');
            form.action = '/instagram/schedule-event/' + eventId;
            form.method = 'POST';
            modal.classList.remove('hidden');
        }

        function closeScheduleModal() {
            document.getElementById('scheduleModal').classList.add('hidden');
        }

        function openRepostModal(eventId) {
            const modal = document.getElementById('repostModal');
            const form = document.getElementById('repostForm');
            form.action = '/instagram/schedule-repost/' + eventId;
            form.method = 'POST';
            modal.classList.remove('hidden');
        }

        function closeRepostModal() {
            document.getElementById('repostModal').classList.add('hidden');
        }

        // Close modals when clicking outside
        document.getElementById('scheduleModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeScheduleModal();
            }
        });

        document.getElementById('repostModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRepostModal();
            }
        });

        // Set minimum datetime for schedule modal
        const datetimeInput = document.querySelector('input[name="instagram_scheduled_at"]');
        if (datetimeInput) {
            const now = new Date();
            now.setMinutes(now.getMinutes() + 5);
            datetimeInput.setAttribute('min', now.toISOString().slice(0, 16));
        }

        // Set minimum datetime for repost modal
        const repostDatetimeInput = document.querySelector('input[name="instagram_repost_at"]');
        if (repostDatetimeInput) {
            const now = new Date();
            now.setMinutes(now.getMinutes() + 5);
            repostDatetimeInput.setAttribute('min', now.toISOString().slice(0, 16));
        }
    </script>
</body>
</html>
