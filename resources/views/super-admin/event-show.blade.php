<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                <p class="text-sm text-purple-200">Super Admin Panel</p>
            </div>
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
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $event->name }}</h2>
                    <p class="text-sm text-gray-600">Event Details</p>
                </div>
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
                <a href="{{ route('super-admin.manage-events') }}" class="text-purple-600 hover:text-purple-800 mb-6 inline-block">← Back to Events</a>

                <div class="grid grid-cols-3 gap-6 mb-6">
                    <!-- Event Image -->
                    <div class="col-span-1">
                        @if($event->event_image)
                        <img src="{{ Storage::url($event->event_image) }}" alt="{{ $event->name }}" class="w-full h-48 object-cover rounded-lg shadow-md">
                        @else
                        <div class="w-full h-48 bg-gray-300 rounded-lg shadow-md flex items-center justify-center">
                            <span class="text-4xl">📷</span>
                        </div>
                        @endif
                    </div>

                    <!-- Event Info -->
                    <div class="col-span-2 bg-white rounded-lg shadow p-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-gray-600 text-sm">Club</p>
                                <p class="text-lg font-semibold text-gray-800">{{ optional($event->club)->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Status</p>
                                @php
                                    $actualStatus = $event->getComputedStatus();
                                    $displayStatus = match ($actualStatus) {
                                        'ongoing' => 'Currently Running',
                                        default => ucfirst($actualStatus),
                                    };
                                    $statusColor = match ($actualStatus) {
                                        'upcoming' => 'yellow',
                                        'ongoing' => 'green',
                                        default => 'gray',
                                    };
                                @endphp
                                <span class="px-3 py-1 bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800 rounded-full text-sm font-semibold">
                                    {{ $displayStatus }}
                                </span>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Date</p>
                                <p class="text-lg font-semibold text-gray-800">{{ \Carbon\Carbon::parse($event->date)->format('M d, Y') }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Time</p>
                                <p class="text-lg font-semibold text-gray-800">{{ $event->start_time }} - {{ $event->end_time }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Location</p>
                                <p class="text-lg font-semibold text-gray-800">{{ $event->location }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Category</p>
                                <p class="text-lg font-semibold text-gray-800">{{ $event->category }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Description</h3>
                    <div class="prose prose-sm max-w-none">
                        {!! \App\Helpers\MarkdownHelper::parse($event->description) !!}
                    </div>
                </div>

                <!-- Event Gallery -->
                @if($event->media->count() > 0)
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Event Gallery ({{ $event->media->count() }} photos)</h3>
                    <div class="grid grid-cols-5 gap-4">
                        @foreach($event->media as $media)
                        <div class="relative rounded-lg overflow-hidden shadow-md hover:shadow-lg transition cursor-pointer group border border-gray-300" onclick="openImageModal(this)">
                            <img src="{{ asset('storage/' . $media->file_path) }}" alt="Event photo" class="w-full h-28 object-cover">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition flex items-center justify-center">
                                <span class="text-white text-2xl opacity-0 group-hover:opacity-100 transition">🔍</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Attendees Section -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800">Attendees ({{ $event->attendances->count() }})</h3>
                    </div>

                    @if($event->attendances->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Name</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Matric No</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Check-in Time</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($event->attendances as $attendance)
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ $attendance->attendee_name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $attendance->matric_no }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $attendance->check_in_time ?? 'Not checked in' }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-3 py-1 bg-{{ $attendance->status === 'Present' ? 'green' : 'red' }}-100 text-{{ $attendance->status === 'Present' ? 'green' : 'red' }}-800 rounded-full text-xs font-semibold">{{ $attendance->status }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="p-6 text-center text-gray-500">
                        No attendees yet
                    </div>
                    @endif
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
