<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/uitm_logo.png') }}?v={{ filemtime(public_path('images/uitm_logo.png')) }}">
    <title>{{ $event->name }} | Campus Event Hub</title>
    @vite('resources/css/app.css')
    <style>
        .prose {
            color: inherit;
        }
        .prose h1, .prose h2, .prose h3, .prose h4, .prose h5, .prose h6 {
            font-weight: 700;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            color: #1f2937;
        }
        .prose h1 { font-size: 1.875rem; }
        .prose h2 { font-size: 1.5rem; }
        .prose h3 { font-size: 1.25rem; }
        .prose h4 { font-size: 1.125rem; }
        .prose h5, .prose h6 { font-size: 1rem; }
        .prose p {
            margin-bottom: 1rem;
            line-height: 1.75;
        }
        .prose ul, .prose ol {
            margin-bottom: 1rem;
            padding-left: 2rem;
        }
        .prose li {
            margin-bottom: 0.5rem;
        }
        .prose strong {
            font-weight: 700;
            color: #1f2937;
        }
        .prose em {
            font-style: italic;
        }
        .prose code {
            background-color: #f3f4f6;
            padding: 0.125rem 0.375rem;
            border-radius: 0.25rem;
            font-family: monospace;
            font-size: 0.9em;
            color: #d946ef;
        }
        .prose pre {
            background-color: #1f2937;
            color: #f3f4f6;
            padding: 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
            margin-bottom: 1rem;
        }
        .prose pre code {
            background-color: transparent;
            color: inherit;
            padding: 0;
        }
        .prose blockquote {
            border-left: 4px solid #d1d5db;
            padding-left: 1rem;
            margin-left: 0;
            margin-bottom: 1rem;
            color: #6b7280;
            font-style: italic;
        }
        .prose a {
            color: #7c3aed;
            text-decoration: underline;
        }
        .prose a:hover {
            color: #6d28d9;
        }
        .prose hr {
            border: none;
            border-top: 2px solid #e5e7eb;
            margin: 2rem 0;
        }
        .prose table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        .prose table thead {
            background-color: #f9fafb;
        }
        .prose table th {
            padding: 0.75rem;
            text-align: left;
            font-weight: 700;
            border: 1px solid #e5e7eb;
        }
        .prose table td {
            padding: 0.75rem;
            border: 1px solid #e5e7eb;
        }
    </style>
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
                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>👕</span>
                    <span>Merchandise</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
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
                <div>
                    <a href="{{ route('event.index') }}" class="text-purple-600 hover:text-purple-700 text-sm mb-1">← Back to Events</a>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $event->name }}</h2>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('event.edit', $event) }}" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                        ✏️ Edit Event
                    </a>
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

            <!-- Event Details -->
            <div class="p-8">
                <!-- Event Image -->
                @if($event->event_image)
                    <div class="mb-8 rounded-lg overflow-hidden shadow-lg">
                        <img src="{{ asset('storage/' . $event->event_image) }}" alt="{{ $event->name }}" class="w-full h-96 object-cover">
                    </div>
                @else
                    <div class="mb-8 rounded-lg overflow-hidden shadow-lg bg-gradient-to-r from-purple-500 to-purple-700 h-96 flex items-center justify-center">
                        <span class="text-6xl opacity-50">📸</span>
                    </div>
                @endif

                <!-- Event Gallery -->
                @if($event->media->count() > 0)
                <div class="mb-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Event Gallery ({{ $event->media->count() }} photos)</h3>
                    <div class="grid grid-cols-4 gap-4">
                        @foreach($event->media as $media)
                        <div class="relative rounded-lg overflow-hidden shadow-md hover:shadow-lg transition cursor-pointer group border border-gray-300" onclick="openImageModal(this)">
                            <img src="{{ asset('storage/' . $media->file_path) }}" alt="Event photo" class="w-full h-32 object-cover">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition flex items-center justify-center">
                                <span class="text-white text-2xl opacity-0 group-hover:opacity-100 transition">🔍</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="grid grid-cols-3 gap-8">
                    <!-- Main Content -->
                    <div class="col-span-2">
                        <div class="bg-white rounded-lg shadow p-8 mb-8">
                            <h3 class="text-2xl font-bold text-gray-800 mb-4">{{ $event->name }}</h3>
                            
                            <div class="grid grid-cols-2 gap-6 mb-8">
                                <div>
                                    <p class="text-gray-600 text-sm font-semibold mb-1">Date</p>
                                    <p class="text-gray-800 font-semibold text-lg">{{ $event->date->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600 text-sm font-semibold mb-1">Time</p>
                                    <p class="text-gray-800 font-semibold text-lg">{{ $event->start_time }} - {{ $event->end_time }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600 text-sm font-semibold mb-1">Location</p>
                                    <p class="text-gray-800 font-semibold text-lg">{{ $event->location }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600 text-sm font-semibold mb-1">Expected Attendees</p>
                                    <p class="text-gray-800 font-semibold text-lg">{{ $event->expected_attendees ?? 'N/A' }}</p>
                                </div>
                            </div>

                            <div class="mb-6">
                                <p class="text-gray-600 text-sm font-semibold mb-2">Description</p>
                                <div class="prose prose-sm max-w-none">
                                    {!! \App\Helpers\MarkdownHelper::parse($event->description) !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div>
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Event Details</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <p class="text-gray-600 text-sm font-semibold mb-1">Category</p>
                                    <span class="px-3 py-1 bg-blue-100 text-blue-600 rounded-full text-xs font-semibold">{{ $event->category }}</span>
                                </div>
                                
                                <div>
                                    <p class="text-gray-600 text-sm font-semibold mb-1">Status</p>
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
                                </div>

                                <div class="pt-4 border-t border-gray-200">
                                    <a href="{{ route('event.edit', $event) }}" class="w-full block text-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold mb-2">
                                        ✏️ Edit Event
                                    </a>
                                    <form method="POST" action="{{ route('event.destroy', $event) }}" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold">
                                            🗑️ Delete Event
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Instagram Engagement Stats --}}
                        @if($event->isPostedToInstagram())
                        <div class="bg-gradient-to-br from-pink-50 to-purple-50 rounded-lg shadow p-6 mt-6 border border-pink-200">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-bold text-gray-800">📷 Instagram Stats</h3>
                                <span class="text-xs px-2 py-1 bg-pink-200 text-pink-700 rounded-full font-semibold">
                                    Posted
                                </span>
                            </div>

                            <div class="space-y-3">
                                <!-- Likes -->
                                <div class="bg-white rounded-lg p-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 font-semibold">❤️ Likes</span>
                                        <span class="text-xl font-bold text-red-500">{{ $event->instagram_likes_count }}</span>
                                    </div>
                                </div>

                                <!-- Comments -->
                                <div class="bg-white rounded-lg p-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 font-semibold">💬 Comments</span>
                                        <span class="text-xl font-bold text-blue-500">{{ $event->instagram_comments_count }}</span>
                                    </div>
                                </div>

                                <!-- Reach -->
                                <div class="bg-white rounded-lg p-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 font-semibold">👁️ Reach</span>
                                        <span class="text-xl font-bold text-green-500">{{ $event->instagram_reach }}</span>
                                    </div>
                                </div>

                                <!-- Impressions -->
                                <div class="bg-white rounded-lg p-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 font-semibold">📊 Impressions</span>
                                        <span class="text-xl font-bold text-purple-500">{{ $event->instagram_impressions }}</span>
                                    </div>
                                </div>

                                <!-- Engagement Rate -->
                                <div class="bg-white rounded-lg p-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 font-semibold">🔥 Engagement</span>
                                        <span class="text-xl font-bold text-orange-500">{{ number_format($event->instagram_engagement_rate, 2) }}%</span>
                                    </div>
                                </div>

                                <!-- Total Engagement -->
                                <div class="bg-gradient-to-r from-pink-100 to-purple-100 rounded-lg p-3 border border-pink-300">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-700 font-bold">Total Interaction</span>
                                        <span class="text-2xl font-bold text-pink-600">{{ $event->getInstagramEngagement() }}</span>
                                    </div>
                                </div>

                                <!-- Last Synced -->
                                @if($event->instagram_last_synced_at)
                                <div class="text-xs text-gray-500 text-center mt-3">
                                    Last updated: {{ $event->instagram_last_synced_at->diffForHumans() }}
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4" onclick="closeImageModal(event)">
        <div class="relative max-w-4xl w-full" onclick="event.stopPropagation()">
            <button onclick="closeImageModal()" class="absolute -top-8 -right-8 text-white text-3xl hover:text-gray-300 transition">×</button>
            <img id="modalImage" src="" alt="Event photo" class="w-full max-h-96 object-contain rounded-lg">
        </div>
    </div>

    <script>
        function openImageModal(element) {
            const imageSrc = element.querySelector('img').src;
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('imageModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal(event) {
            if (event && event.target.id !== 'imageModal') return;
            document.getElementById('imageModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });
    </script>
</body>
</html>
