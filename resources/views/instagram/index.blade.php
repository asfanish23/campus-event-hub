<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Media Management | Campus Event Hub</title>
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
                <a href="{{ route('social-media.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-purple-500 hover:bg-purple-400 transition text-sm font-medium">
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
                            <h1 class="text-3xl font-bold text-gray-900">📣 Social Media Management</h1>
                            <p class="text-gray-600 mt-2">Publish and track your event posts across platforms</p>
                        </div>
                        <a href="{{ route('instagram.settings') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center gap-2">
                            ⚙️ Settings
                        </a>
                    </div>
                </div>

                <!-- Status Alert -->
                @php
                    $threadsConnected = $threadsAccount && $threadsAccount->isTokenValid();
                @endphp
                @if(!$hasInstagramCredentials && !$hasFacebookCredentials && !$hasThreadsCredentials && !$threadsConnected)
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                ⚠️
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-800">
                                    No social media credentials are configured. Please add your credentials in 
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
                                    Ready platforms:
                                    @if($hasInstagramCredentials) Instagram @endif
                                    @if($hasInstagramCredentials && ($hasFacebookCredentials || $hasThreadsCredentials || $threadsConnected)) and @endif
                                    @if($hasFacebookCredentials) Facebook @endif
                                    @if(($hasInstagramCredentials || $hasFacebookCredentials) && ($hasThreadsCredentials || $threadsConnected)) and @endif
                                    @if($hasThreadsCredentials || $threadsConnected) Threads @endif
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
                    <form method="GET" action="{{ route('social-media.index') }}" class="space-y-4">
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
                            <a href="{{ route('social-media.index') }}" class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-semibold">
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
                                @php
                                    $instagramPosted = $event->isPostedToInstagram();
                                    $facebookPosted = $event->isPostedToFacebook();
                                    $threadsPosted = $event->isPostedToPlatform('threads');
                                    $instagramPostedAt = $event->postedAtForPlatform('instagram') ?? $event->instagram_posted_at;
                                    $facebookPostedAt = $event->postedAtForPlatform('facebook');
                                    $threadsPostedAt = $event->postedAtForPlatform('threads');
                                    $latestInstagramPost = $event->latestSocialPost('instagram');
                                    $latestFacebookPost = $event->latestSocialPost('facebook');
                                    $latestThreadsPost = $event->latestSocialPost('threads');
                                    $instagramPermalink = $latestInstagramPost?->permalink;
                                    $threadsPermalink = $latestThreadsPost?->permalink;
                                    $hasPendingInstagramSchedule = $event->instagram_auto_post && $event->instagram_scheduled_at && !$event->instagram_scheduled_posted && !$instagramPosted;
                                    $hasCompletedInstagramSchedule = $event->instagram_scheduled_at && ($event->instagram_scheduled_posted || $instagramPosted);
                                    $facebookPostUrl = null;

                                    if ($latestFacebookPost && $latestFacebookPost->platform_post_id) {
                                        $facebookPostId = $latestFacebookPost->platform_post_id;
                                        if (str_contains($facebookPostId, '_')) {
                                            [$fbPageId, $fbInnerPostId] = explode('_', $facebookPostId, 2);
                                            $facebookPostUrl = 'https://www.facebook.com/' . $fbPageId . '/posts/' . $fbInnerPostId;
                                        } else {
                                            $facebookPostUrl = 'https://www.facebook.com/' . $facebookPostId;
                                        }
                                    }
                                @endphp
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
                                            @php
                                                $computedStatus = $event->getComputedStatus();
                                                $displayStatus = match ($computedStatus) {
                                                    'ongoing' => 'Currently Running',
                                                    default => ucfirst($computedStatus),
                                                };
                                            @endphp
                                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold
                                                @if($computedStatus === 'upcoming')
                                                    bg-blue-100 text-blue-800
                                                @elseif($computedStatus === 'ongoing')
                                                    bg-green-100 text-green-800
                                                @else
                                                    bg-gray-100 text-gray-800
                                                @endif
                                            ">
                                                {{ $displayStatus }}
                                            </span>

                                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold text-white bg-gradient-to-r from-pink-500 to-purple-600">
                                                Instagram
                                            </span>
                                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold text-white bg-blue-600">
                                                Facebook
                                            </span>
                                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold text-white bg-gray-800">
                                                Threads
                                            </span>
                                        </div>

                                        <div class="grid grid-cols-2 gap-2 text-xs mb-4">
                                            <div class="bg-gray-50 border border-gray-200 rounded px-2 py-2">
                                                <span class="font-semibold">Instagram:</span>
                                                <span class="{{ $instagramPosted ? 'text-green-700' : 'text-gray-600' }}">{{ $instagramPosted ? 'Posted' : 'Not Posted' }}</span>
                                            </div>
                                            <div class="bg-gray-50 border border-gray-200 rounded px-2 py-2">
                                                <span class="font-semibold">Facebook:</span>
                                                <span class="{{ $facebookPosted ? 'text-green-700' : 'text-gray-600' }}">{{ $facebookPosted ? 'Posted' : 'Not Posted' }}</span>
                                            </div>
                                            <div class="bg-gray-50 border border-gray-200 rounded px-2 py-2">
                                                <span class="font-semibold">Threads:</span>
                                                <span class="{{ $threadsPosted ? 'text-green-700' : 'text-gray-600' }}">{{ $threadsPosted ? 'Posted' : 'Not Posted' }}</span>
                                            </div>
                                        </div>

                                        <button type="button"
                                            data-open-social-modal="{{ $event->id }}"
                                                class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                                            Manage Social Media
                                        </button>
                                    </div>
                                </div>

                                <!-- Social Media Modal -->
                                <div id="socialModal-{{ $event->id }}" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center p-4">
                                    <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
                                        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                                            <div>
                                                <h3 class="text-xl font-bold text-gray-900">Manage Social Media</h3>
                                                <p class="text-sm text-gray-600">{{ $event->name }}</p>
                                            </div>
                                            <button type="button" data-close-social-modal="{{ $event->id }}" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">&times;</button>
                                        </div>

                                        <div class="p-6 space-y-6">
                                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                                <!-- Instagram Section -->
                                                <section class="border border-pink-200 rounded-lg p-4 bg-pink-50">
                                                    <div class="flex items-center justify-between mb-3">
                                                        <h4 class="text-lg font-semibold text-gray-900">Instagram</h4>
                                                        <span class="px-2 py-1 text-xs font-semibold rounded-full text-white bg-gradient-to-r from-pink-500 to-purple-600">Instagram</span>
                                                    </div>

                                                    <p class="text-sm text-gray-800"><span class="font-semibold">Status:</span> {{ $instagramPosted ? 'Posted' : 'Not Posted' }}</p>
                                                    <p class="text-xs text-gray-600 mt-1"><span class="font-semibold">Last posted:</span> {{ $instagramPostedAt ? $instagramPostedAt->format('M d, Y H:i') : 'N/A' }}</p>

                                                    @if($hasPendingInstagramSchedule)
                                                        <div class="mt-3 text-xs bg-yellow-100 border border-yellow-300 rounded p-2 text-yellow-800">
                                                            Scheduled Post: {{ $event->instagram_scheduled_at->format('M d, Y H:i') }}
                                                        </div>
                                                    @endif

                                                    @if($hasCompletedInstagramSchedule)
                                                        <div class="mt-3 text-xs bg-green-100 border border-green-300 rounded p-2 text-green-800">
                                                            Scheduled post has already been published.
                                                        </div>
                                                    @endif

                                                    @if($event->instagram_repost_at && !$event->instagram_reposted)
                                                        <div class="mt-2 text-xs bg-yellow-100 border border-yellow-300 rounded p-2 text-yellow-800">
                                                            Scheduled Repost: {{ $event->instagram_repost_at->format('M d, Y H:i') }}
                                                        </div>
                                                    @endif

                                                    <div class="mt-4 space-y-2">
                                                        <form action="{{ route('social-media.post.instagram', $event->id) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="w-full bg-gradient-to-r from-pink-500 via-purple-500 to-pink-600 hover:from-pink-600 hover:via-purple-600 hover:to-pink-700 text-white text-sm font-semibold py-2 px-3 rounded-lg transition">
                                                                {{ $instagramPosted ? 'Repost' : 'Post Now' }}
                                                            </button>
                                                        </form>

                                                        @if(!$hasPendingInstagramSchedule)
                                                            <button type="button"
                                                                data-toggle-inline-form="ig-schedule-{{ $event->id }}"
                                                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 px-3 rounded-lg transition">
                                                                Schedule Post
                                                            </button>

                                                            <div id="ig-schedule-{{ $event->id }}" class="hidden bg-white border border-blue-200 rounded p-2">
                                                                <form action="{{ route('instagram.schedule-event', $event) }}" method="POST" class="space-y-2">
                                                                    @csrf
                                                                    <input type="datetime-local" name="instagram_scheduled_at" required min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}" class="w-full px-3 py-2 text-sm border border-gray-300 rounded">
                                                                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 rounded">Confirm Schedule</button>
                                                                </form>
                                                            </div>
                                                        @endif

                                                        @if($instagramPosted)
                                                                <button type="button"
                                                                    data-toggle-inline-form="ig-repost-{{ $event->id }}"
                                                                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2 px-3 rounded-lg transition">
                                                                Schedule Repost
                                                            </button>

                                                            <div id="ig-repost-{{ $event->id }}" class="hidden bg-white border border-indigo-200 rounded p-2">
                                                                <form action="{{ route('instagram.schedule-repost', $event) }}" method="POST" class="space-y-2">
                                                                    @csrf
                                                                    <input type="datetime-local" name="instagram_repost_at" required min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}" class="w-full px-3 py-2 text-sm border border-gray-300 rounded">
                                                                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2 rounded">Confirm Repost Schedule</button>
                                                                </form>
                                                            </div>
                                                        @endif

                                                        @if($hasPendingInstagramSchedule)
                                                            <form action="{{ route('instagram.cancel-scheduled', $event) }}" method="POST">
                                                                @csrf
                                                                <button type="submit" onclick="return confirm('Cancel scheduled post?')" class="w-full bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-2 px-3 rounded-lg transition">
                                                                    Cancel Scheduled Post
                                                                </button>
                                                            </form>
                                                        @endif

                                                        @if($event->instagram_repost_at && !$event->instagram_reposted)
                                                            <form action="{{ route('instagram.cancel-repost-schedule', $event) }}" method="POST">
                                                                @csrf
                                                                <button type="submit" onclick="return confirm('Cancel scheduled repost?')" class="w-full bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-2 px-3 rounded-lg transition">
                                                                    Cancel Scheduled Repost
                                                                </button>
                                                            </form>
                                                        @endif

                                                        @if($instagramPermalink)
                                                            <a href="{{ $instagramPermalink }}" target="_blank" class="block w-full text-center bg-gray-800 hover:bg-black text-white text-sm font-semibold py-2 px-3 rounded-lg transition">
                                                                View Post
                                                            </a>
                                                        @else
                                                            <button type="button" disabled class="block w-full text-center bg-gray-300 text-gray-600 text-sm font-semibold py-2 px-3 rounded-lg cursor-not-allowed" title="Post URL not available">
                                                                Post URL not available
                                                            </button>
                                                        @endif
                                                    </div>
                                                </section>

                                                <!-- Facebook Section -->
                                                <section class="border border-blue-200 rounded-lg p-4 bg-blue-50">
                                                    <div class="flex items-center justify-between mb-3">
                                                        <h4 class="text-lg font-semibold text-gray-900">Facebook</h4>
                                                        <span class="px-2 py-1 text-xs font-semibold rounded-full text-white bg-blue-600">Facebook</span>
                                                    </div>

                                                    <p class="text-sm text-gray-800"><span class="font-semibold">Status:</span> {{ $facebookPosted ? 'Posted' : 'Not Posted' }}</p>
                                                    <p class="text-xs text-gray-600 mt-1"><span class="font-semibold">Last posted:</span> {{ $facebookPostedAt ? $facebookPostedAt->format('M d, Y H:i') : 'N/A' }}</p>

                                                    <div class="mt-4 space-y-2">
                                                        <form action="{{ route('social-media.post.facebook', $event->id) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 px-3 rounded-lg transition">
                                                                {{ $facebookPosted ? 'Repost' : 'Post Now' }}
                                                            </button>
                                                        </form>

                                                        @if($facebookPostUrl)
                                                            <a href="{{ $facebookPostUrl }}" target="_blank" class="block w-full text-center bg-gray-800 hover:bg-black text-white text-sm font-semibold py-2 px-3 rounded-lg transition">
                                                                View Post
                                                            </a>
                                                        @endif
                                                    </div>
                                                </section>

                                                <!-- Threads Section -->
                                                <section class="border border-gray-300 rounded-lg p-4 bg-gray-50">
                                                    <div class="flex items-center justify-between mb-3">
                                                        <h4 class="text-lg font-semibold text-gray-900">Threads</h4>
                                                        <span class="px-2 py-1 text-xs font-semibold rounded-full text-white bg-gray-800">Threads</span>
                                                    </div>

                                                    @if($threadsConnected)
                                                        <p class="text-sm text-gray-800"><span class="font-semibold">Status:</span> {{ $threadsPosted ? 'Posted' : 'Not Posted' }}</p>
                                                        <p class="text-xs text-gray-600 mt-1"><span class="font-semibold">Last posted:</span> {{ $threadsPostedAt ? $threadsPostedAt->format('M d, Y H:i') : 'N/A' }}</p>
                                                        <p class="text-xs text-gray-600 mt-1"><span class="font-semibold">Connected as:</span> {{ $threadsAccount->threads_username }}</p>

                                                        <div class="mt-4 space-y-2">
                                                            <form action="{{ route('social-media.post.threads', $event->id) }}" method="POST">
                                                                @csrf
                                                                <button type="submit" class="w-full bg-gray-800 hover:bg-black text-white text-sm font-semibold py-2 px-3 rounded-lg transition">
                                                                    {{ $threadsPosted ? 'Repost' : 'Post Now' }}
                                                                </button>
                                                            </form>

                                                            @if($threadsPermalink)
                                                                <a href="{{ $threadsPermalink }}" target="_blank" class="block w-full text-center bg-gray-800 hover:bg-black text-white text-sm font-semibold py-2 px-3 rounded-lg transition">
                                                                    View Post
                                                                </a>
                                                            @else
                                                                <button type="button" disabled class="block w-full text-center bg-gray-300 text-gray-600 text-sm font-semibold py-2 px-3 rounded-lg cursor-not-allowed" title="Post URL not available">
                                                                    Post URL not available
                                                                </button>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <p class="text-sm text-gray-800"><span class="font-semibold">Status:</span> Not Connected</p>
                                                        <p class="text-xs text-gray-600 mt-1">Connect your Threads account to publish event posts to your club's Threads feed.</p>

                                                        <div class="mt-4">
                                                            <a href="{{ route('threads.oauth.redirect', $threadsClubId) }}" class="block w-full text-center bg-gray-800 hover:bg-black text-white text-sm font-semibold py-2 px-3 rounded-lg transition">
                                                                🔗 Connect Threads
                                                            </a>
                                                        </div>
                                                    @endif
                                                </section>
                                            </div>

                                            <div class="border-t border-gray-200 pt-4 flex flex-col sm:flex-row gap-2 sm:justify-end">
                                                <form action="{{ route('social-media.publish-all', $event->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="w-full sm:w-auto bg-gray-900 hover:bg-black text-white font-semibold py-2 px-4 rounded-lg transition">
                                                        Publish to All Platforms
                                                    </button>
                                                </form>
                                                <button type="button" data-close-social-modal="{{ $event->id }}" class="w-full sm:w-auto bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-lg transition">
                                                    Close
                                                </button>
                                            </div>
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

    <script>
        function openSocialModal(eventId) {
            const modal = document.getElementById('socialModal-' + eventId);
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }

        function closeSocialModal(eventId) {
            const modal = document.getElementById('socialModal-' + eventId);
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        function toggleInlineForm(formId) {
            const form = document.getElementById(formId);
            if (form) {
                form.classList.toggle('hidden');
            }
        }

        document.addEventListener('click', function (event) {
            const openButton = event.target.closest('[data-open-social-modal]');
            if (openButton) {
                openSocialModal(openButton.getAttribute('data-open-social-modal'));
                return;
            }

            const closeButton = event.target.closest('[data-close-social-modal]');
            if (closeButton) {
                closeSocialModal(closeButton.getAttribute('data-close-social-modal'));
                return;
            }

            const toggleButton = event.target.closest('[data-toggle-inline-form]');
            if (toggleButton) {
                toggleInlineForm(toggleButton.getAttribute('data-toggle-inline-form'));
                return;
            }

            const modal = event.target;
            if (modal && modal.id && modal.id.startsWith('socialModal-')) {
                if (event.target === modal) {
                    const eventId = modal.id.replace('socialModal-', '');
                    closeSocialModal(eventId);
                }
            }
        });
    </script>

</body>
</html>
