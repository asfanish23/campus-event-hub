<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/UITM_FAVICON.png') }}?v={{ filemtime(public_path('images/UITM_FAVICON.png')) }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit {{ $event->name }} | Campus Event Hub</title>
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
                    <h2 class="text-2xl font-bold text-gray-800">Edit Event</h2>
                    <p class="text-sm text-gray-600">{{ $event->name }}</p>
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

                <div class="bg-white rounded-lg shadow p-6 mb-6 max-w-4xl flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">QR Attendance</p>
                        <p class="text-lg font-semibold {{ $event->qr_active ? 'text-green-700' : 'text-gray-700' }}">
                            {{ $event->qr_active ? 'Active' : 'Inactive' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Toggle QR attendance without leaving this page.</p>
                    </div>
                    <form method="POST" action="{{ route('super-admin.events.toggle-qr', $event->id) }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $event->qr_active ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-600 text-white hover:bg-green-700' }}">
                            {{ $event->qr_active ? 'Deactivate QR' : 'Activate QR' }}
                        </button>
                    </form>
                </div>

                <form method="POST" action="{{ route('super-admin.events.update', $event) }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-8 max-w-4xl">
                    @csrf
                    @method('PUT')

                    @if ($errors->any())
                    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4">
                        <p class="font-semibold text-red-700 mb-2">Please fix the following before saving:</p>
                        <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Event Name *</label>
                            <input type="text" name="name" value="{{ $event->name }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 @error('name') border-red-500 @enderror">
                            @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Club *</label>
                            <select name="club_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 @error('club_id') border-red-500 @enderror">
                                <option value="">Select a club</option>
                                @foreach($clubs as $club)
                                <option value="{{ $club->id }}" {{ $event->club_id === $club->id ? 'selected' : '' }}>{{ $club->name }}</option>
                                @endforeach
                            </select>
                            @error('club_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                        <textarea name="description" required rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 @error('description') border-red-500 @enderror">{{ $event->description }}</textarea>
                        @error('description') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                            <input type="date" name="date" value="{{ $event->date->format('Y-m-d') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 @error('date') border-red-500 @enderror">
                            @error('date') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                            <input type="text" name="category" value="{{ $event->category }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 @error('category') border-red-500 @enderror">
                            @error('category') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Start Time *</label>
                            <input type="time" name="start_time" value="{{ \Carbon\Carbon::parse($event->start_time)->format('H:i') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 @error('start_time') border-red-500 @enderror">
                            @error('start_time') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">End Time *</label>
                            <input type="time" name="end_time" value="{{ \Carbon\Carbon::parse($event->end_time)->format('H:i') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 @error('end_time') border-red-500 @enderror">
                            @error('end_time') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Location *</label>
                            <input type="text" name="location" value="{{ $event->location }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 @error('location') border-red-500 @enderror">
                            @error('location') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                            <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 @error('status') border-red-500 @enderror">
                                @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ $event->status === $status ? 'selected' : '' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                            @error('status') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Expected Attendees</label>
                        <input type="number" name="expected_attendees" value="{{ $event->expected_attendees }}" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 @error('expected_attendees') border-red-500 @enderror">
                        @error('expected_attendees') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Event Gallery Section -->
                <div class="mt-8 pb-8 border-b border-gray-200">
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
                        @error('event_photos') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        @error('event_photos.*') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        <p class="text-xs text-gray-500 mt-2">Accepted formats: JPEG, PNG, JPG, GIF (Max 5MB per file)</p>
                        
                        <div id="photosPreview" class="mt-6 grid grid-cols-3 gap-4"></div>
                    </div>
                </div>

                    <div class="flex gap-4 mt-8">
                        <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-medium">Save Changes</button>
                        <a href="{{ route('super-admin.manage-events') }}" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition font-medium">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
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
