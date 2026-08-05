<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/uitm_logo.png') }}?v={{ filemtime(public_path('images/uitm_logo.png')) }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit Event | Campus Event Hub</title>
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
                <h2 class="text-2xl font-bold text-gray-800">Edit Event</h2>
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

            <!-- Edit Form -->
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
                    <form method="POST" action="{{ route('event.update', $event) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Event Image Section -->
                        <div class="mb-8 pb-8 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-800 mb-6">Event Image</h3>
                            <div>
                                @if($event->event_image)
                                    <div class="mb-4 rounded-lg overflow-hidden border-2 border-gray-300">
                                        <img src="{{ asset('storage/' . $event->event_image) }}" alt="{{ $event->name }}" class="w-full h-48 object-cover">
                                    </div>
                                @endif
                                <div id="imagePreview" class="mb-4 rounded-lg overflow-hidden border-2 border-gray-300" style="display: none;">
                                    <img id="previewImg" src="" alt="Preview" class="w-full h-48 object-cover">
                                </div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">Change Event Image</label>
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
                                    <input type="text" name="name" value="{{ old('name', $event->name) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Category *</label>
                                    <select name="category" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                        <option value="">Select a category</option>
                                        <option value="Academic" {{ old('category', $event->category) === 'Academic' ? 'selected' : '' }}>Academic</option>
                                        <option value="Sports" {{ old('category', $event->category) === 'Sports' ? 'selected' : '' }}>Sports</option>
                                        <option value="Culture" {{ old('category', $event->category) === 'Culture' ? 'selected' : '' }}>Culture</option>
                                        <option value="Technology" {{ old('category', $event->category) === 'Technology' ? 'selected' : '' }}>Technology</option>
                                        <option value="Volunteer" {{ old('category', $event->category) === 'Volunteer' ? 'selected' : '' }}>Volunteer</option>
                                        <option value="Leadership" {{ old('category', $event->category) === 'Leadership' ? 'selected' : '' }}>Leadership</option>
                                        <option value="Religious" {{ old('category', $event->category) === 'Religious' ? 'selected' : '' }}>Religious</option>
                                        <option value="Entrepreneurship" {{ old('category', $event->category) === 'Entrepreneurship' ? 'selected' : '' }}>Entrepreneurship</option>
                                        <option value="Arts & Media" {{ old('category', $event->category) === 'Arts & Media' ? 'selected' : '' }}>Arts & Media</option>
                                        <option value="Others" {{ old('category', $event->category) === 'Others' ? 'selected' : '' }}>Others</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Event Date *</label>
                                    <input type="date" name="date" value="{{ old('date', $event->date->format('Y-m-d')) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Location *</label>
                                    <input type="text" name="location" value="{{ old('location', $event->location) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Start Time *</label>
                                    <input type="time" name="start_time" value="{{ old('start_time', \Carbon\Carbon::parse($event->start_time)->format('H:i')) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">End Time *</label>
                                    <input type="time" name="end_time" value="{{ old('end_time', \Carbon\Carbon::parse($event->end_time)->format('H:i')) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status *</label>
                                    <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                        <option value="Upcoming" {{ old('status', $event->status) === 'Upcoming' ? 'selected' : '' }}>Upcoming</option>
                                        <option value="Currently Running" {{ old('status', $event->status) === 'Currently Running' ? 'selected' : '' }}>Currently Running</option>
                                        <option value="Completed" {{ old('status', $event->status) === 'Completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Expected Attendees</label>
                                    <input type="number" name="expected_attendees" value="{{ old('expected_attendees', $event->expected_attendees) }}" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                </div>
                            </div>

                            <div class="mt-6">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Description *</label>
                                <textarea name="description" rows="5" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">{{ old('description', $event->description) }}</textarea>
                            </div>
                        </div>

                        <!-- Event Gallery Section -->
                        <div class="mb-8 pb-8 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-800 mb-6">Event Gallery (Multiple Photos)</h3>
                            
                            <!-- Existing Photos -->
                            @if($event->media->count() > 0)
                            <div class="mb-6">
                                <h4 class="text-sm font-semibold text-gray-700 mb-3">Existing Photos</h4>
                                <div class="grid grid-cols-3 gap-4" id="existingPhotos">
                                    @foreach($event->media as $media)
                                    <div class="relative group photo-item" data-media-id="{{ $media->id }}">
                                        <img src="{{ asset('storage/' . $media->file_path) }}" alt="Event photo" class="w-full h-32 object-cover rounded-lg border border-gray-300">
                                        <button type="button" class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white p-2 rounded opacity-0 group-hover:opacity-100 transition delete-photo" data-media-id="{{ $media->id }}">
                                            <span class="text-xs font-semibold">Delete</span>
                                        </button>
                                    </div>
                                    @endforeach
                                </div>
                                <p class="text-xs text-gray-500 mt-2">Hover and click delete to remove photos</p>
                            </div>
                            @endif
                            
                            <!-- Add New Photos -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">Upload Additional Photos</label>
                                <input type="file" name="event_photos[]" accept="image/*" multiple id="eventPhotosInput" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-2">Accepted formats: JPEG, PNG, JPG, GIF (Max 5MB per file)</p>
                                
                                <div id="photosPreview" class="mt-6 grid grid-cols-3 gap-4"></div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-4">
                            <button type="submit" class="px-8 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                                Save Changes
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

        // Delete photo instantly
        document.querySelectorAll('.delete-photo').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const mediaId = this.getAttribute('data-media-id');
                const photoItem = this.closest('.photo-item');

                if (confirm('Are you sure you want to delete this photo?')) {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    
                    if (!csrfToken) {
                        alert('CSRF token not found. Please refresh the page.');
                        return;
                    }

                    fetch(`/event-media/${mediaId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        if (response.ok) {
                            photoItem.remove();
                            alert('Photo deleted successfully!');
                        } else {
                            return response.json().then(data => {
                                alert('Failed to delete photo: ' + (data.error || response.statusText));
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error deleting photo: ' + error.message);
                    });
                }
            });
        });
    </script>
</body>
</html>
