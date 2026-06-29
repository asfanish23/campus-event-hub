<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
<body class="min-h-screen bg-gray-50">
    <div
        id="eventData"
        data-event-id="{{ (int) $event->id }}"
        data-event-date="{{ $event->date?->format('Y-m-d') }}"
        data-start-time="{{ optional($event->start_time)->format('H:i:s') }}"
        data-end-time="{{ optional($event->end_time)->format('H:i:s') }}"
    ></div>

    {{-- Navigation --}}
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('student.dashboard') }}" class="text-2xl font-bold text-purple-600">Campus Event Hub</a>
            </div>
            <div class="flex items-center gap-6">
                <a href="{{ route('student.profile.show') }}" class="text-gray-700 hover:text-purple-600 font-semibold">👤 My Profile</a>
                <span class="text-gray-700">Welcome, <strong>{{ auth()->user()->name }}</strong></span>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto px-6 py-8">

        {{-- Back Button --}}
        <a href="{{ route('student.dashboard') }}" class="inline-flex items-center gap-2 text-purple-600 hover:text-purple-800 mb-6 font-semibold">
            ← Back to Events
        </a>

        {{-- Event Header --}}
        @if($event->event_image)
            <div class="w-full h-80 bg-gradient-to-r from-purple-600 to-purple-800 rounded-2xl overflow-hidden mb-8 shadow-lg">
                <img src="{{ asset('storage/' . $event->event_image) }}" alt="{{ $event->name }}" class="w-full h-full object-cover">
            </div>
        @else
            <div class="w-full h-80 bg-gradient-to-r from-purple-600 to-purple-800 rounded-2xl flex items-center justify-center mb-8 shadow-lg">
                <span class="text-7xl">📅</span>
            </div>
        @endif

        {{-- Event Title and Status --}}
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-4xl font-bold text-gray-800">{{ $event->name }}</h1>
                <span id="statusBadge" class="px-4 py-2 bg-purple-100 text-purple-800 rounded-full text-sm font-semibold">
                    {{ $event->status }}
                </span>
            </div>
            <p class="text-xl text-gray-600">{{ $event->category }}</p>
        </div>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">

            {{-- Left Column: Event Details --}}
            <div class="lg:col-span-2">

                {{-- Description --}}
                <div class="bg-white rounded-xl shadow-md p-8 mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">About This Event</h2>
                    <div class="text-gray-700 leading-relaxed prose prose-sm max-w-none">
                        {!! \App\Helpers\MarkdownHelper::parse($event->description) !!}
                    </div>
                </div>

                {{-- Key Information --}}
                <div class="bg-white rounded-xl shadow-md p-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Event Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Date & Time --}}
                        <div class="flex items-start gap-4">
                            <div class="text-3xl">📅</div>
                            <div>
                                <p class="text-gray-600 font-semibold">Date</p>
                                <p class="text-gray-800 text-lg">{{ $event->date->format('F d, Y') }}</p>
                            </div>
                        </div>

                        {{-- Start Time --}}
                        <div class="flex items-start gap-4">
                            <div class="text-3xl">🕐</div>
                            <div>
                                <p class="text-gray-600 font-semibold">Start Time</p>
                                <p class="text-gray-800 text-lg">
                                    @php
                                        try {
                                            $time = is_string($event->start_time) ? \Carbon\Carbon::createFromFormat('H:i:s', $event->start_time) : $event->start_time;
                                            echo $time->format('H:i');
                                        } catch(\Exception $e) {
                                            echo 'TBA';
                                        }
                                    @endphp
                                </p>
                            </div>
                        </div>

                        {{-- End Time --}}
                        <div class="flex items-start gap-4">
                            <div class="text-3xl">🕒</div>
                            <div>
                                <p class="text-gray-600 font-semibold">End Time</p>
                                <p class="text-gray-800 text-lg">
                                    @php
                                        try {
                                            $time = is_string($event->end_time) ? \Carbon\Carbon::createFromFormat('H:i:s', $event->end_time) : $event->end_time;
                                            echo $time->format('H:i');
                                        } catch(\Exception $e) {
                                            echo 'TBA';
                                        }
                                    @endphp
                                </p>
                            </div>
                        </div>

                        {{-- Location --}}
                        <div class="flex items-start gap-4">
                            <div class="text-3xl">📍</div>
                            <div>
                                <p class="text-gray-600 font-semibold">Location</p>
                                <p class="text-gray-800 text-lg">{{ $event->location }}</p>
                            </div>
                        </div>

                        {{-- Category --}}
                        <div class="flex items-start gap-4">
                            <div class="text-3xl">🏷️</div>
                            <div>
                                <p class="text-gray-600 font-semibold">Category</p>
                                <p class="text-gray-800 text-lg">{{ ucfirst($event->category) }}</p>
                            </div>
                        </div>

                        {{-- Expected Attendees --}}
                        <div class="flex items-start gap-4">
                            <div class="text-3xl">👥</div>
                            <div>
                                <p class="text-gray-600 font-semibold">Expected Attendees</p>
                                <p class="text-gray-800 text-lg">{{ $event->expected_attendees ?? 'TBA' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Sidebar --}}
            <div class="space-y-6">

                {{-- Registration Card --}}
                <div class="bg-gradient-to-br from-purple-600 to-purple-800 rounded-xl shadow-lg p-8 text-white">
                    <h3 class="text-2xl font-bold mb-4">Interested?</h3>
                    <p class="text-purple-100 mb-6">Register now to attend this amazing event and get updates!</p>
                    @if($isRegistered)
                        <button class="w-full py-3 bg-green-400 hover:bg-red-600 text-white rounded-lg font-semibold text-lg transition" id="registerBtn" data-event-id="{{ (int) $event->id }}">
                            <span id="registerBtnText">✓ Already Registered</span>
                        </button>
                    @else
                        <button class="w-full py-3 bg-white text-purple-600 rounded-lg hover:bg-gray-100 transition font-semibold text-lg" id="registerBtn" data-event-id="{{ (int) $event->id }}">
                            Register for Event
                        </button>
                    @endif
                </div>

                {{-- Like Card --}}
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Like This Event</h3>
                    <button
                        id="likeBtn"
                        data-event-id="{{ (int) $event->id }}"
                        class="w-full py-3 flex items-center justify-center gap-2 rounded-lg font-semibold text-lg transition {{ $isLiked ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                    >
                        <span id="likeIcon">{{ $isLiked ? '❤️' : '🤍' }}</span>
                        <span id="likeText">{{ $isLiked ? 'Unlike' : 'Like' }}</span>
                        <span id="likeCount" class="text-sm">{{ $likeCount }}</span>
                    </button>
                </div>

                {{-- Event Stats --}}
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Event Stats</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-600">Status</span>
                            <span id="statsBadge" class="font-semibold text-gray-800">{{ $event->status }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-600">Expected Attendees</span>
                            <span class="font-semibold text-gray-800">{{ $event->expected_attendees ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-600">Duration</span>
                            <span class="font-semibold text-gray-800">
                                @php
                                    try {
                                        $startTime = is_string($event->start_time) ? \Carbon\Carbon::createFromFormat('H:i:s', $event->start_time) : $event->start_time;
                                        $endTime = is_string($event->end_time) ? \Carbon\Carbon::createFromFormat('H:i:s', $event->end_time) : $event->end_time;
                                        $duration = $endTime->diffInMinutes($startTime);
                                        echo floor($duration / 60) . 'h ' . ($duration % 60) . 'm';
                                    } catch(\Exception $e) {
                                        echo 'TBA';
                                    }
                                @endphp
                            </span>
                        </div>
                        @if($event->getComputedStatus() === 'completed')
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-600">Average Rating</span>
                            <span class="font-semibold text-gray-800">{{ number_format((float) $reviews->avg('rating'), 1) }}/5</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-600">Total Reviews</span>
                            <span class="font-semibold text-gray-800">{{ $reviews->count() }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Share --}}
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Share Event</h3>
                    <div class="flex gap-3">
                        <button onclick="shareEvent()" class="flex-1 py-2 px-3 bg-purple-100 text-purple-600 rounded-lg hover:bg-purple-200 transition font-semibold text-sm">
                            📱 Share
                        </button>
                        <button onclick="copyLink()" class="flex-1 py-2 px-3 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition font-semibold text-sm">
                            🔗 Copy Link
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if($event->getComputedStatus() === 'completed')
        <div class="bg-white rounded-xl shadow-md p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Reviews & Ratings</h2>
            <p class="text-gray-500 mb-6">{{ $reviews->count() }} reviews · Average {{ number_format((float) $reviews->avg('rating'), 1) }}/5</p>

            @if(session('success'))
                <div class="mb-4 rounded-lg bg-green-100 text-green-800 px-4 py-3">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-lg bg-red-100 text-red-800 px-4 py-3">{{ session('error') }}</div>
            @endif

            @if($reviewEligibility['can_submit_review'])
                <form method="POST" action="{{ route('student.event.review', $event) }}" class="mb-8 border border-gray-200 rounded-xl p-5">
                    @csrf
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Leave Your Review</h3>
                    <div class="mb-3">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Rating</label>
                        <select name="rating" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                            <option value="">Select rating</option>
                            <option value="5">5 - Excellent</option>
                            <option value="4">4 - Good</option>
                            <option value="3">3 - Average</option>
                            <option value="2">2 - Poor</option>
                            <option value="1">1 - Very Poor</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Review</label>
                        <textarea name="comment" rows="4" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent" placeholder="Share your experience"></textarea>
                    </div>
                    <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">Submit Review</button>
                </form>
            @elseif($reviewEligibility['existing_review'])
                @php $myReview = $reviewEligibility['existing_review']; @endphp
                <div class="mb-8 border border-green-200 bg-green-50 rounded-xl p-5">
                    <h3 class="text-lg font-semibold text-green-800 mb-2">Your Review</h3>
                    <p class="text-sm text-green-700 mb-2">{{ str_repeat('⭐', (int) $myReview->rating) }} ({{ $myReview->rating }}/5)</p>
                    <p class="text-gray-700">{{ $myReview->comment ?? $myReview->review_text }}</p>
                </div>
            @else
                <div class="mb-8 border border-amber-200 bg-amber-50 rounded-xl p-5 text-amber-800">
                    You can review this event only after you joined, recorded attendance, and the event is completed.
                </div>
            @endif

            <div class="space-y-4">
                @forelse($reviews as $review)
                    <div class="border border-gray-200 rounded-xl p-4">
                        <div class="flex justify-between items-center mb-2">
                            <p class="font-semibold text-gray-800">{{ $review->user->name ?? $review->reviewer_name }}</p>
                            <p class="text-xs text-gray-500">{{ $review->created_at?->format('M d, Y') }}</p>
                        </div>
                        <p class="text-sm text-yellow-500 mb-2">{{ str_repeat('⭐', (int) $review->rating) }}</p>
                        <p class="text-gray-700">{{ $review->comment ?? $review->review_text }}</p>
                    </div>
                @empty
                    <p class="text-gray-500">No reviews yet.</p>
                @endforelse
            </div>
        </div>
        @endif

    </div>

    {{-- Similar Events Section --}}
    <div class="max-w-5xl mx-auto px-6 py-8">
        @if($similarEvents->count() > 0)
            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-2xl shadow-md border border-indigo-200 p-8">
                <div class="flex items-center gap-3 mb-6">
                    <h3 class="text-2xl font-bold text-gray-900">🔗 Similar Events</h3>
                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-indigo-200 text-indigo-800">
                        {{ $similarEvents->count() }} events
                    </span>
                </div>
                <p class="text-gray-600 mb-6">You might also be interested in these events:</p>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    @foreach($similarEvents as $similarEvent)
                        @php
                            $badge = strtolower($similarEvent->status ?? '');
                            $badgeClass = 'bg-purple-100 text-purple-800';
                            if ($badge === 'ongoing') $badgeClass = 'bg-green-100 text-green-800';
                            if ($badge === 'completed') $badgeClass = 'bg-gray-100 text-gray-700';
                        @endphp
                        <a href="{{ route('student.event.show', $similarEvent->id) }}" class="group">
                            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg hover:border-purple-300 transition h-full flex flex-col">
                                {{-- Image --}}
                                <div class="relative">
                                    @if($similarEvent->event_image)
                                        <img src="{{ asset('storage/' . $similarEvent->event_image) }}" alt="{{ $similarEvent->name }}" class="w-full h-32 object-cover">
                                    @else
                                        <div class="w-full h-32 bg-purple-100 flex items-center justify-center text-2xl">📅</div>
                                    @endif
                                    <div class="absolute top-2 left-2">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                            {{ ucfirst($similarEvent->status) }}
                                        </span>
                                    </div>
                                </div>
                                
                                {{-- Content --}}
                                <div class="p-3 flex-1 flex flex-col">
                                    <h4 class="font-bold text-sm text-gray-900 group-hover:text-purple-700 transition line-clamp-2">
                                        {{ $similarEvent->name }}
                                    </h4>
                                    
                                    <div class="mt-2 text-xs text-gray-600 space-y-1">
                                        <p class="flex items-center gap-1">
                                            <span>📅</span>
                                            <span>{{ optional($similarEvent->date)->format('M d') ?? 'TBA' }}</span>
                                        </p>
                                        <p class="flex items-center gap-1">
                                            <span>📍</span>
                                            <span class="truncate">{{ $similarEvent->location ?? 'TBA' }}</span>
                                        </p>
                                    </div>
                                    
                                    <div class="mt-3 pt-3 border-t border-gray-100">
                                        <button class="similar-like-btn like-btn-{{ $similarEvent->id }} w-full px-2 py-1 rounded text-xs font-semibold transition {{ in_array($similarEvent->id, $likedEventIds) ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                                            data-event-id="{{ (int) $similarEvent->id }}">
                                            <span class="like-icon-{{ $similarEvent->id }}">{{ in_array($similarEvent->id, $likedEventIds) ? '❤️' : '🤍' }}</span>
                                            <span class="like-count-{{ $similarEvent->id }}">{{ $similarEvent->likes()->count() }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <script>
        // Function to calculate actual event status based on current date/time
        function getActualStatus(dateStr, startTimeStr, endTimeStr) {
            const today = new Date();
            const eventDate = new Date(dateStr);
            
            // Normalize today to midnight for date comparison
            const todayMidnight = new Date(today.getFullYear(), today.getMonth(), today.getDate());
            
            // If event date is in the past (before today)
            if (eventDate < todayMidnight) {
                return 'Completed';
            }

            // If event date is today
            if (eventDate.getTime() === todayMidnight.getTime()) {
                // Check if event has started and not ended
                if (startTimeStr) {
                    const eventStart = new Date(`${dateStr}T${startTimeStr}`);
                    if (eventStart <= today) {
                        if (!endTimeStr) {
                            return 'Currently Running';
                        }
                        const eventEnd = new Date(`${dateStr}T${endTimeStr}`);
                        if (eventEnd > today) {
                            return 'Currently Running';
                        } else {
                            return 'Completed';
                        }
                    }
                }
            }

            // Otherwise it's upcoming
            return 'Upcoming';
        }

        // Update status badges on page load
        function updateStatusDisplay(eventDate, startTime, endTime) {
            
            const actualStatus = getActualStatus(eventDate, startTime, endTime);
            
            // Update status badge at top
            const statusBadge = document.getElementById('statusBadge');
            if (statusBadge) {
                statusBadge.textContent = actualStatus;
                statusBadge.className = 'px-4 py-2 rounded-full text-sm font-semibold';
                
                if (actualStatus === 'Upcoming') {
                    statusBadge.classList.add('bg-blue-100', 'text-blue-700');
                } else if (actualStatus === 'Currently Running') {
                    statusBadge.classList.add('bg-green-100', 'text-green-700');
                } else if (actualStatus === 'Completed') {
                    statusBadge.classList.add('bg-gray-100', 'text-gray-700');
                }
            }
            
            // Update status in Event Stats
            const statsBadge = document.getElementById('statsBadge');
            if (statsBadge) {
                statsBadge.textContent = actualStatus;
            }
        }

        function bindRegisterButton(eventId) {
            const btn = document.getElementById('registerBtn');
            const textSpan = document.getElementById('registerBtnText');
            if (!btn) {
                return;
            }

            btn.onclick = function () {
                if (btn.classList.contains('bg-green-400')) {
                    cancelRegistration(eventId);
                } else {
                    registerEvent(eventId);
                }
            };

            if (btn && textSpan && btn.classList.contains('bg-green-400')) {
                btn.addEventListener('mouseenter', function() {
                    textSpan.textContent = '✕ Cancel Registration';
                });
                btn.addEventListener('mouseleave', function() {
                    textSpan.textContent = '✓ Already Registered';
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const eventDataElement = document.getElementById('eventData');
            const eventId = Number(eventDataElement?.dataset?.eventId || 0);
            const eventDate = eventDataElement?.dataset?.eventDate || '';
            const startTime = eventDataElement?.dataset?.startTime || '';
            const endTime = eventDataElement?.dataset?.endTime || '';

            updateStatusDisplay(eventDate, startTime, endTime);
            bindRegisterButton(eventId);

            const likeBtn = document.getElementById('likeBtn');
            if (likeBtn) {
                likeBtn.addEventListener('click', function () {
                    toggleLike(eventId);
                });
            }

            document.querySelectorAll('.similar-like-btn').forEach((button) => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const targetEventId = Number(button.dataset.eventId || 0);
                    if (targetEventId > 0) {
                        toggleLike(targetEventId);
                    }
                });
            });
        });

        async function registerEvent(eventId) {
            const btn = document.getElementById('registerBtn');
            if (btn) btn.disabled = true;
            
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const response = await fetch(`/student/event/${eventId}/register`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('✓ ' + data.message);
                    // Change button to show registered status
                    if (btn) {
                        btn.outerHTML = `
                            <button class="w-full py-3 bg-green-400 hover:bg-red-600 text-white rounded-lg font-semibold text-lg transition" id="registerBtn" data-event-id="${eventId}">
                                <span id="registerBtnText">✓ Already Registered</span>
                            </button>
                        `;
                        bindRegisterButton(eventId);
                    }
                } else {
                    alert('❌ ' + (data.message || 'Registration failed'));
                    if (btn) btn.disabled = false;
                }
            } catch(error) {
                console.error('Error:', error);
                alert('Error registering for event: ' + error.message);
                if (btn) btn.disabled = false;
            }
        }

        async function cancelRegistration(eventId) {
            if (!confirm('Are you sure you want to cancel your registration?')) {
                return;
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const response = await fetch(`/student/event/${eventId}/cancel-registration`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('✓ ' + data.message);
                    // Change button back to register
                    const btn = document.getElementById('registerBtn');
                    if (btn) {
                        btn.outerHTML = `
                            <button class="w-full py-3 bg-white text-purple-600 rounded-lg hover:bg-gray-100 transition font-semibold text-lg" id="registerBtn" data-event-id="${eventId}">
                                Register for Event
                            </button>
                        `;
                        bindRegisterButton(eventId);
                    }
                } else {
                    alert('✗ ' + (data.message || 'Cancellation failed'));
                }
            } catch(error) {
                console.error('Error:', error);
                alert('Error cancelling registration: ' + error.message);
            }
        }

        function shareEvent() {
            if (navigator.share) {
                navigator.share({
                    title: '{{ $event->name }}',
                    text: 'Check out this event!',
                    url: window.location.href
                });
            } else {
                alert('Sharing is not supported on this browser');
            }
        }

        function copyLink() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                alert('Link copied to clipboard!');
            });
        }

        async function toggleLike(eventId) {
            const likeBtn = document.getElementById('likeBtn');
            const likeIcon = document.getElementById('likeIcon');
            const likeText = document.getElementById('likeText');
            const likeCount = document.getElementById('likeCount');
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
                    // Update icon, text, and count
                    likeIcon.textContent = isLiked ? '🤍' : '❤️';
                    likeText.textContent = isLiked ? 'Like' : 'Unlike';
                    likeCount.textContent = data.likeCount;
                    
                    // Update button styling
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
