<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Club | Campus Event Hub</title>
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
                <h2 class="text-2xl font-bold text-gray-800">Edit Club</h2>
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

                <!-- Background Photo Preview (Full Width) -->
                <div class="mb-8 -mx-8">
                    @if($club->background_photo)
                        @php
                            $posH = 50 + (($club->background_position_h ?? 0) / 2);
                            $posV = 50 + (($club->background_position_v ?? 0) / 2);
                        @endphp
                        <div id="backgroundPreviewContainer" style="height: 256px; background-image: url('{{ asset('storage/' . $club->background_photo) }}'); background-position: {{ $posH }}% {{ $posV }}%; background-size: cover; background-repeat: no-repeat; cursor: grab; user-select: none;"></div>
                    @else
                        <div id="backgroundPreviewContainer" class="w-full bg-gradient-to-r from-purple-500 to-purple-700 flex items-center justify-center text-5xl opacity-50" style="height: 256px;">
                            📸
                        </div>
                    @endif
                </div>

                <div class="max-w-4xl bg-white rounded-lg shadow p-8">
                    <form method="POST" action="{{ route('super-admin.clubs.update', $club) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Profile Photo Section -->
                        <div class="mb-8 pb-8 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-800 mb-6">Club Profile Photo</h3>
                            <div class="flex items-center gap-6">
                                <div class="flex-shrink-0">
                                    @if($club->profile_photo)
                                        <img id="profilePhotoPreview" src="{{ asset('storage/' . $club->profile_photo) }}" alt="{{ $club->name }}" class="w-32 h-32 rounded-lg object-cover border-2 border-gray-300">
                                    @else
                                        <div id="profilePhotoPreview" class="w-32 h-32 bg-gradient-to-br from-purple-500 to-purple-700 rounded-lg flex items-center justify-center text-5xl border-2 border-gray-300">
                                            🏢
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <label class="block text-sm font-semibold text-gray-700 mb-3">Upload Club Photo</label>
                                    <input type="file" id="profilePhotoInput" name="profile_photo" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent" onchange="previewProfilePhoto(this)">
                                    <p class="text-xs text-gray-500 mt-2">Accepted formats: JPEG, PNG, JPG, GIF (Max 2MB)</p>
                                </div>
                            </div>
                        </div>

                        <!-- Background Photo Section -->
                        <div class="mb-8 pb-8 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-800 mb-6">Club Background Photo</h3>
                            <div>
                                <!-- Positioning Controls -->
                                <div id="positioningControls" class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200" @if($club->background_photo) style="display: block;" @else style="display: none;" @endif>
                                    <p class="text-sm font-semibold text-gray-700 mb-4">Drag or adjust vertical position</p>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="text-xs text-gray-600 font-semibold">Vertical Position</label>
                                            <input type="range" id="bgVertical" min="-100" max="100" value="{{ $club->background_position_v ?? 0 }}" class="w-full">
                                            <p class="text-xs text-gray-500 mt-1">Position: <span id="bgVerticalValue">{{ $club->background_position_v ?? 0 }}</span>%</p>
                                        </div>
                                    </div>
                                    <button type="button" onclick="resetBackgroundPosition()" class="mt-4 px-4 py-2 bg-purple-600 text-white rounded text-sm font-semibold hover:bg-purple-700 transition w-full">
                                        Reset Position
                                    </button>
                                </div>

                                <!-- Hidden Inputs for Position Data -->
                                <input type="hidden" id="bgPositionV" name="background_position_v" value="{{ $club->background_position_v ?? 0 }}">

                                <label class="block text-sm font-semibold text-gray-700 mb-3">Upload Background Photo</label>
                                <input type="file" id="backgroundPhotoInput" name="background_photo" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent" onchange="previewBackgroundPhoto(this)">
                                <p class="text-xs text-gray-500 mt-2">Accepted formats: JPEG, PNG, JPG, GIF (Max 5MB)</p>
                            </div>
                        </div>

                        <!-- Club Information Section -->
                        <div class="mb-8">
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Club Name *</label>
                                    <input type="text" name="name" value="{{ old('name', $club->name) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                                    <input type="email" name="email" value="{{ old('email', $club->email) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Category *</label>
                                    <select name="category" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                        <option value="">Select a category</option>
                                        <option value="Academic" {{ old('category', $club->category) === 'Academic' ? 'selected' : '' }}>Academic</option>
                                        <option value="Sports" {{ old('category', $club->category) === 'Sports' ? 'selected' : '' }}>Sports</option>
                                        <option value="Culture" {{ old('category', $club->category) === 'Culture' ? 'selected' : '' }}>Culture</option>
                                        <option value="Technology" {{ old('category', $club->category) === 'Technology' ? 'selected' : '' }}>Technology</option>
                                        <option value="Volunteer" {{ old('category', $club->category) === 'Volunteer' ? 'selected' : '' }}>Volunteer</option>
                                        <option value="Leadership" {{ old('category', $club->category) === 'Leadership' ? 'selected' : '' }}>Leadership</option>
                                        <option value="Religious" {{ old('category', $club->category) === 'Religious' ? 'selected' : '' }}>Religious</option>
                                        <option value="Entrepreneurship" {{ old('category', $club->category) === 'Entrepreneurship' ? 'selected' : '' }}>Entrepreneurship</option>
                                        <option value="Arts & Media" {{ old('category', $club->category) === 'Arts & Media' ? 'selected' : '' }}>Arts & Media</option>
                                        <option value="Others" {{ old('category', $club->category) === 'Others' ? 'selected' : '' }}>Others</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-6">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Description *</label>
                                <textarea name="description" rows="5" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">{{ old('description', $club->description) }}</textarea>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-4">
                            <button type="submit" class="px-8 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                                Save Changes
                            </button>
                            <a href="{{ route('super-admin.manage-clubs') }}" class="px-8 py-3 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-semibold">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        let isDraggingBg = false;
        let dragStartY = 0;
        let currentBgV = 0;

        function updateBackgroundPosition() {
            const container = document.getElementById('backgroundPreviewContainer');
            if (!container) return;

            currentBgV = parseFloat(document.getElementById('bgVertical')?.value || 0);

            // Calculate background-position (vertical only, horizontal stays at 50%)
            const posV = 50 + (currentBgV / 2);
            
            container.style.backgroundPosition = `50% ${posV}%`;

            document.getElementById('bgVerticalValue').textContent = Math.round(currentBgV);
            document.getElementById('bgPositionV').value = currentBgV;
        }

        function setupBackgroundDragAndDrop() {
            const container = document.getElementById('backgroundPreviewContainer');

            if (!container) return;

            container.addEventListener('mousedown', (e) => {
                isDraggingBg = true;
                dragStartY = e.clientY;
                container.style.cursor = 'grabbing';
            });

            document.addEventListener('mousemove', (e) => {
                if (!isDraggingBg) return;
                
                const deltaY = (e.clientY - dragStartY) / 5;
                currentBgV = Math.max(-100, Math.min(100, currentBgV + deltaY));
                
                dragStartY = e.clientY;
                
                document.getElementById('bgVertical').value = currentBgV;
                updateBackgroundPosition();
            });

            document.addEventListener('mouseup', () => {
                isDraggingBg = false;
                container.style.cursor = 'grab';
            });
        }

        function resetBackgroundPosition() {
            currentBgV = 0;
            document.getElementById('bgVertical').value = 0;
            updateBackgroundPosition();
        }

        function previewProfilePhoto(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const preview = document.getElementById('profilePhotoPreview');
                    if (preview.tagName === 'DIV') {
                        const newImg = document.createElement('img');
                        newImg.id = 'profilePhotoPreview';
                        newImg.src = e.target.result;
                        newImg.alt = 'Club Profile';
                        newImg.className = 'w-32 h-32 rounded-lg object-cover border-2 border-gray-300';
                        preview.parentElement.replaceChild(newImg, preview);
                    } else {
                        preview.src = e.target.result;
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function previewBackgroundPhoto(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const container = document.getElementById('backgroundPreviewContainer');
                    const controlsDiv = document.getElementById('positioningControls');
                    const bgVSlider = document.getElementById('bgVertical');
                    
                    // Update background-image
                    container.style.backgroundImage = `url('${e.target.result}')`;
                    container.style.backgroundPosition = '50% 50%';
                    container.style.backgroundSize = 'cover';
                    container.style.backgroundRepeat = 'no-repeat';
                    container.style.cursor = 'grab';
                    
                    // Reset slider
                    if (bgVSlider) bgVSlider.value = 0;
                    currentBgV = 0;
                    
                    controlsDiv.style.display = 'block';
                    updateBackgroundPosition();
                    setupBackgroundDragAndDrop();
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Event listeners for range inputs
        document.addEventListener('DOMContentLoaded', function() {
            const bgVSlider = document.getElementById('bgVertical');
            const container = document.getElementById('backgroundPreviewContainer');

            if (bgVSlider) {
                bgVSlider.addEventListener('input', () => {
                    updateBackgroundPosition();
                });
            }

            // Apply positioning to existing image
            if (container && container.style.backgroundImage) {
                // Load saved positioning value from hidden input
                const savedV = parseFloat(document.getElementById('bgPositionV')?.value || 0);
                currentBgV = savedV;
                
                // Set slider value to saved vertical position
                if (bgVSlider) bgVSlider.value = savedV;
                
                console.log('Background loaded with vertical position:', savedV);
                updateBackgroundPosition();
                setupBackgroundDragAndDrop();
            }
        });
    </script>
</body>
</html>
