<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event | Campus Event Hub</title>
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
                <h2 class="text-2xl font-bold text-gray-800">Create Event</h2>
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

            <!-- Create Form -->
            <div class="p-8">
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        <h3 class="font-semibold mb-2">There were errors in your submission:</h3>
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="max-w-4xl bg-white rounded-lg shadow p-8">
                    <form method="POST" action="{{ route('event.store') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Event Image Section -->
                        <div class="mb-8 pb-8 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-800 mb-6">Event Image</h3>
                            <div>
                                <div id="imagePreview" class="mb-4 rounded-lg overflow-hidden border-2 border-gray-300 bg-gray-100" style="display: none;">
                                    <img id="previewImg" src="" alt="Preview" class="w-full h-48 object-cover">
                                </div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">Upload Event Image</label>
                                <input type="file" name="event_image" accept="image/*" id="eventImageInput" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-2">Accepted formats: JPEG, PNG, JPG, GIF (Max 5MB)</p>
                            </div>
                        </div>

                        <!-- Event Information Section -->
                        <div class="mb-8 pb-8 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-800 mb-6">Event Information</h3>
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Event Name *</label>
                                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Category *</label>
                                    <select name="category" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                        <option value="">Select a category</option>
                                        <option value="Academic" {{ old('category') === 'Academic' ? 'selected' : '' }}>Academic</option>
                                        <option value="Sports" {{ old('category') === 'Sports' ? 'selected' : '' }}>Sports</option>
                                        <option value="Culture" {{ old('category') === 'Culture' ? 'selected' : '' }}>Culture</option>
                                        <option value="Technology" {{ old('category') === 'Technology' ? 'selected' : '' }}>Technology</option>
                                        <option value="Volunteer" {{ old('category') === 'Volunteer' ? 'selected' : '' }}>Volunteer</option>
                                        <option value="Leadership" {{ old('category') === 'Leadership' ? 'selected' : '' }}>Leadership</option>
                                        <option value="Religious" {{ old('category') === 'Religious' ? 'selected' : '' }}>Religious</option>
                                        <option value="Entrepreneurship" {{ old('category') === 'Entrepreneurship' ? 'selected' : '' }}>Entrepreneurship</option>
                                        <option value="Arts & Media" {{ old('category') === 'Arts & Media' ? 'selected' : '' }}>Arts & Media</option>
                                        <option value="Others" {{ old('category') === 'Others' ? 'selected' : '' }}>Others</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Event Date *</label>
                                    <input type="date" name="date" value="{{ old('date') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Location *</label>
                                    <input type="text" name="location" value="{{ old('location') }}" required placeholder="e.g., Auditorium, Room 101" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Start Time *</label>
                                    <input type="time" name="start_time" value="{{ old('start_time') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">End Time *</label>
                                    <input type="time" name="end_time" value="{{ old('end_time') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status *</label>
                                    <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                        <option value="">Select Status</option>
                                        <option value="Upcoming" {{ old('status') === 'Upcoming' ? 'selected' : '' }}>Upcoming</option>
                                        <option value="Currently Running" {{ old('status') === 'Currently Running' ? 'selected' : '' }}>Currently Running</option>
                                        <option value="Completed" {{ old('status') === 'Completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Expected Attendees</label>
                                    <input type="number" name="expected_attendees" value="{{ old('expected_attendees') }}" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                </div>
                            </div>

                            <div class="mt-6">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Description *</label>
                                <div style="position: relative;">
                                    <textarea id="eventDescription" name="description" rows="5" required placeholder="(Optional) Add extra details before generating! E.g., free breakfast, free merchandise, limited seats, special perks, etc." maxlength="500" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-purple-600 transition-all">{{ old('description') }}</textarea>
                                    
                                    <button type="button" id="generateBtn" 
                                            style="position: absolute; bottom: 10px; right: 10px; border: none; background: #6e45e2; color: white; padding: 6px 14px; border-radius: 20px; font-size: 12px; cursor: pointer; font-weight: 500; transition: all 0.3s ease; hover:background: #5a36c7;">
                                        ✨ Generate with AI
                                    </button>
                                </div>
                                <small style="color: #888; display: block; margin-top: 6px;">💡 Tip: Add any special details (free food, limited spots, prizes, etc.) above. Gemini will use them to create a better description! (Max 500 characters)</small>
                                <small id="aiStatus" style="color: #888; display: none; margin-top: 8px; display: block;">⏳ Gemini is thinking...</small>

                                <!-- Tweak Buttons -->
                                <div id="tweakContainer" style="display: none; margin-top: 12px; gap: 8px; display: none; flex-wrap: wrap;">
                                    <button type="button" class="tweak-btn" data-style="funnier" style="padding: 6px 12px; border: 1px solid #ddd; background: white; color: #666; border-radius: 16px; font-size: 11px; cursor: pointer; transition: all 0.2s ease;">😄 Funnier</button>
                                    <button type="button" class="tweak-btn" data-style="professional" style="padding: 6px 12px; border: 1px solid #ddd; background: white; color: #666; border-radius: 16px; font-size: 11px; cursor: pointer; transition: all 0.2s ease;">📋 Professional</button>
                                    <button type="button" class="tweak-btn" data-style="shorter" style="padding: 6px 12px; border: 1px solid #ddd; background: white; color: #666; border-radius: 16px; font-size: 11px; cursor: pointer; transition: all 0.2s ease;">✂️ Shorter</button>
                                </div>
                            </div>
                        </div>

                        <!-- Multiple Photos Section -->
                        <div class="mb-8 pb-8 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-800 mb-6">Event Gallery (Multiple Photos)</h3>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">Upload Additional Photos</label>
                                <input type="file" name="event_photos[]" accept="image/*" multiple id="eventPhotosInput" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-2">Accepted formats: JPEG, PNG, JPG, GIF (Max 5MB per file). You can select multiple files.</p>
                                
                                <div id="photosPreview" class="mt-6 grid grid-cols-3 gap-4"></div>
                            </div>
                        </div>

                        <!-- Instagram Posting Section -->
                        <div class="mb-8 pb-8 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-800 mb-6">📱 Instagram Auto-Posting</h3>
                            
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                                <div class="flex items-start gap-3">
                                    <span class="text-xl mt-0.5">ℹ️</span>
                                    <p class="text-sm text-blue-800">Automatically post your event to Instagram and track engagement metrics in real-time.</p>
                                </div>
                            </div>

                            <!-- Auto-Post Checkbox -->
                            <div class="mb-6">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="instagram_auto_post" id="instagramAutoPostCheckbox" value="1" class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-2 focus:ring-purple-600">
                                    <span class="text-sm font-semibold text-gray-700">Auto-post to Instagram when event is created</span>
                                </label>
                                <p class="text-xs text-gray-500 mt-2 ml-8">Posts immediately to your club's Instagram account</p>
                            </div>

                            <!-- Schedule Options (shown when checkbox is checked) -->
                            <div id="instagramScheduleContainer" style="display: none;" class="bg-gray-50 border border-gray-300 rounded-lg p-6">
                                <div class="mb-6">
                                    <div class="flex items-center gap-3 mb-4">
                                        <input type="radio" name="instagram_post_timing" id="postImmediately" value="immediate" checked class="w-4 h-4 text-purple-600 border-gray-300 focus:ring-2 focus:ring-purple-600">
                                        <label for="postImmediately" class="text-sm font-semibold text-gray-700">Post Immediately</label>
                                    </div>
                                    <p class="text-xs text-gray-500 ml-7">Post to Instagram right away when creating the event</p>
                                </div>

                                <div class="mb-6">
                                    <div class="flex items-center gap-3 mb-4">
                                        <input type="radio" name="instagram_post_timing" id="postScheduled" value="scheduled" class="w-4 h-4 text-purple-600 border-gray-300 focus:ring-2 focus:ring-purple-600">
                                        <label for="postScheduled" class="text-sm font-semibold text-gray-700">Schedule Post for Later</label>
                                    </div>
                                    <p class="text-xs text-gray-500 ml-7 mb-3">Choose a specific date and time to post</p>
                                    
                                    <div id="scheduleDateTimeContainer" style="display: none;" class="ml-7 space-y-3">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Schedule Date & Time</label>
                                            <input type="datetime-local" name="instagram_scheduled_at" id="instagramScheduledAt" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                            <p class="text-xs text-gray-500 mt-2">Select when you want the event to be posted</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <p class="text-xs text-gray-500 mt-4">💡 Tip: Make sure your event has a cover image before enabling Instagram posting. The system will post the main event image to your club's Instagram account.</p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-4">
                            <button type="submit" class="px-8 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                                Create Event
                            </button>
                            <a href="{{ route('event.index') }}" class="px-8 py-3 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-semibold">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        const textArea = document.getElementById('eventDescription');
        const generateBtn = document.getElementById('generateBtn');
        const aiStatus = document.getElementById('aiStatus');
        const tweakContainer = document.getElementById('tweakContainer');
        const tweakBtns = document.querySelectorAll('.tweak-btn');

        // ============================================
        // INSTAGRAM AUTO-POSTING SECTION
        // ============================================
        const instagramAutoPostCheckbox = document.getElementById('instagramAutoPostCheckbox');
        const instagramScheduleContainer = document.getElementById('instagramScheduleContainer');
        const postImmediatelyRadio = document.getElementById('postImmediately');
        const postScheduledRadio = document.getElementById('postScheduled');
        const scheduleDateTimeContainer = document.getElementById('scheduleDateTimeContainer');
        const instagramScheduledAt = document.getElementById('instagramScheduledAt');

        // Show/hide Instagram section when checkbox changes
        instagramAutoPostCheckbox.addEventListener('change', function() {
            if (this.checked) {
                instagramScheduleContainer.style.display = 'block';
            } else {
                instagramScheduleContainer.style.display = 'none';
                instagramScheduledAt.value = ''; // Clear scheduled time if unchecked
            }
        });

        // Show/hide schedule datetime when "Schedule Post for Later" is selected
        postScheduledRadio.addEventListener('change', function() {
            if (this.checked) {
                scheduleDateTimeContainer.style.display = 'block';
            }
        });

        postImmediatelyRadio.addEventListener('change', function() {
            if (this.checked) {
                scheduleDateTimeContainer.style.display = 'none';
            }
        });

        // Set minimum datetime to now + 5 minutes
        instagramScheduledAt.addEventListener('click', function() {
            const now = new Date();
            now.setMinutes(now.getMinutes() + 5);
            const minDateTime = now.toISOString().slice(0, 16);
            instagramScheduledAt.setAttribute('min', minDateTime);
        });

        // ============================================
        // 1. TYPING EFFECT FUNCTION (Kesan Mengetik)
        // ============================================
        function typeEffect(element, text, speed = 15) {
            let i = 0;
            element.value = "";
            
            // Add pulsing animation to border
            element.style.borderColor = '#a78bfa';
            element.style.boxShadow = '0 0 0 3px rgba(167, 139, 250, 0.1)';
            
            return new Promise((resolve) => {
                const timer = setInterval(() => {
                    if (i < text.length) {
                        element.value += text.charAt(i);
                        i++;
                        element.scrollTop = element.scrollHeight;
                    } else {
                        clearInterval(timer);
                        element.style.borderColor = '#d1d5db';
                        element.style.boxShadow = 'none';
                        resolve();
                    }
                }, speed);
            });
        }

        // ============================================
        // 2. GENERATE DESCRIPTION (Backend Call)
        // ============================================
        generateBtn.addEventListener('click', async () => {
            const eventName = document.querySelector('[name="name"]').value;
            const category = document.querySelector('[name="category"]').value;
            const location = document.querySelector('[name="location"]').value;
            const attendees = document.querySelector('[name="expected_attendees"]').value;
            const extraDetails = textArea.value.trim();

            if (!eventName) {
                alert("Please enter Event Name first!");
                return;
            }

            // Visual feedback: disable button and change text
            generateBtn.disabled = true;
            generateBtn.style.opacity = '0.6';
            generateBtn.textContent = '⏳ Gemini is writing...';
            aiStatus.style.display = 'block';
            tweakContainer.style.display = 'none';

            try {
                const response = await fetch('{{ route("ai.generate-description") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        event_name: eventName,
                        category: category,
                        location: location,
                        attendees: attendees ? parseInt(attendees) : null,
                        extra_details: extraDetails
                    })
                });

                const data = await response.json();

                if (data.success) {
                    await typeEffect(textArea, data.text);
                    tweakContainer.style.display = 'flex';
                } else {
                    alert("Error: " + (data.error || "Could not generate description"));
                }
            } catch (error) {
                console.error("Error:", error);
                alert("Something went wrong with the AI. Please try again.");
            } finally {
                generateBtn.disabled = false;
                generateBtn.style.opacity = '1';
                generateBtn.textContent = '✨ Generate with AI';
                aiStatus.style.display = 'none';
            }
        });

        // ============================================
        // 3. TWEAK BUTTONS (Ubah Gaya Deskripsi)
        // ============================================
        tweakBtns.forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                
                const style = btn.dataset.style;
                const currentText = textArea.value;

                if (!currentText.trim()) {
                    alert("Please generate a description first!");
                    return;
                }

                // Visual feedback
                btn.disabled = true;
                btn.style.opacity = '0.5';
                const originalText = btn.textContent;
                btn.textContent = '⏳...';

                try {
                    const response = await fetch('{{ route("ai.tweak-description") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            text: currentText,
                            style: style
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        await typeEffect(textArea, data.text);
                    } else {
                        alert("Error: " + (data.error || "Could not tweak description"));
                    }
                } catch (error) {
                    console.error("Error:", error);
                    alert("Something went wrong. Please try again.");
                } finally {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    btn.textContent = originalText;
                }
            });
        });

        // ============================================
        // 4. ADD CSRF TOKEN TO HEAD IF NOT EXISTS
        // ============================================
        if (!document.querySelector('meta[name="csrf-token"]')) {
            const meta = document.createElement('meta');
            meta.name = 'csrf-token';
            meta.content = '{{ csrf_token() }}';
            document.head.appendChild(meta);
        }

        // ============================================
        // 5. IMAGE PREVIEW HANDLERS
        // ============================================
        document.getElementById('eventImageInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('previewImg').src = event.target.result;
                    document.getElementById('imagePreview').style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });

        // Handle multiple photos preview
        document.getElementById('eventPhotosInput').addEventListener('change', function(e) {
            const previewDiv = document.getElementById('photosPreview');
            previewDiv.innerHTML = '';
            const files = Array.from(e.target.files);
            
            files.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const photoDiv = document.createElement('div');
                    photoDiv.className = 'relative';
                    photoDiv.innerHTML = `
                        <img src="${event.target.result}" alt="Photo ${index + 1}" class="w-full h-32 object-cover rounded-lg border border-gray-300">
                        <span class="absolute top-1 right-1 bg-purple-600 text-white text-xs px-2 py-1 rounded">${index + 1}</span>
                    `;
                    previewDiv.appendChild(photoDiv);
                }
                reader.readAsDataURL(file);
            });
        });

        // ============================================
        // 6. ENHANCED FORM STYLING
        // ============================================
        textArea.addEventListener('focus', function() {
            this.style.borderColor = '#9333ea';
            this.style.boxShadow = '0 0 0 3px rgba(147, 51, 234, 0.1)';
        });

        textArea.addEventListener('blur', function() {
            this.style.borderColor = '#d1d5db';
            this.style.boxShadow = 'none';
        });

        // Hover effects for tweak buttons
        tweakBtns.forEach(btn => {
            btn.addEventListener('mouseover', function() {
                this.style.background = '#f3f4f6';
                this.style.borderColor = '#9333ea';
            });
            
            btn.addEventListener('mouseout', function() {
                this.style.background = 'white';
                this.style.borderColor = '#ddd';
            });
        });
    </script>
</body>
</html>
