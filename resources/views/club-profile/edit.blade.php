<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Club Profile | Campus Event Hub</title>
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
                <a href="{{ route('club-profile.show') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-purple-500 hover:bg-purple-400 transition text-sm font-medium">
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
                <h2 class="text-2xl font-bold text-gray-800">Edit Club Profile</h2>
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
                
                @if(!$club || !$club->id)
                    <div class="mb-6 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded">
                        <h3 class="font-semibold">⚠️ Warning: Club data not found</h3>
                        <p>Club ID: {{ $club->id ?? 'NULL' }}</p>
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
                    <form method="POST" action="{{ route('club-profile.update') }}" enctype="multipart/form-data">
                        @csrf

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
                                    <input type="text" name="name" value="{{ old('name', $club->name ?? '') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Category *</label>
                                    <select name="category" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                        <option value="">Select a category</option>
                                        <option value="Academic" {{ old('category', $club->category ?? '') === 'Academic' ? 'selected' : '' }}>Academic</option>
                                        <option value="Sports" {{ old('category', $club->category ?? '') === 'Sports' ? 'selected' : '' }}>Sports</option>
                                        <option value="Culture" {{ old('category', $club->category ?? '') === 'Culture' ? 'selected' : '' }}>Culture</option>
                                        <option value="Technology" {{ old('category', $club->category ?? '') === 'Technology' ? 'selected' : '' }}>Technology</option>
                                        <option value="Volunteer" {{ old('category', $club->category ?? '') === 'Volunteer' ? 'selected' : '' }}>Volunteer</option>
                                        <option value="Leadership" {{ old('category', $club->category ?? '') === 'Leadership' ? 'selected' : '' }}>Leadership</option>
                                        <option value="Religious" {{ old('category', $club->category ?? '') === 'Religious' ? 'selected' : '' }}>Religious</option>
                                        <option value="Entrepreneurship" {{ old('category', $club->category ?? '') === 'Entrepreneurship' ? 'selected' : '' }}>Entrepreneurship</option>
                                        <option value="Arts & Media" {{ old('category', $club->category ?? '') === 'Arts & Media' ? 'selected' : '' }}>Arts & Media</option>
                                        <option value="Others" {{ old('category', $club->category ?? '') === 'Others' ? 'selected' : '' }}>Others</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                                    <input type="email" name="email" value="{{ old('email', $club->email ?? '') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Total Members</label>
                                    <input type="number" name="total_members" value="{{ old('total_members', $club->total_members ?? 0) }}" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                </div>
                            </div>

                            <div class="mt-6">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Description *</label>
                                <textarea name="description" rows="5" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">{{ old('description', $club->description ?? '') }}</textarea>
                            </div>

                            @if($club->founded_date)
                                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">📅 Founded</label>
                                    <p class="text-gray-800 font-medium">{{ $club->founded_date->format('F d, Y') }}</p>
                                    <p class="text-xs text-gray-500 mt-1">Only super admins can modify this date</p>
                                </div>
                            @endif
                        </div>

                        <!-- President Information Section -->
                        <div class="mb-8 pb-8 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-800 mb-6">President Information</h3>
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">President Name *</label>
                                    <input type="text" name="president_name" value="{{ old('president_name', $club->president_name ?? '') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Contact Number *</label>
                                    <input type="text" name="president_contact" value="{{ old('president_contact', $club->president_contact ?? '') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                </div>
                            </div>
                        </div>

                        <!-- Social Media Section -->
                        <div class="mb-8">
                            <h3 class="text-lg font-bold text-gray-800 mb-6">Social Media Links</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">📘 Facebook URL</label>
                                    <input type="url" name="facebook_url" value="{{ old('facebook_url', $club->facebook_url ?? '') }}" placeholder="https://facebook.com/..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">📷 Instagram URL</label>
                                    <input type="url" name="instagram_url" value="{{ old('instagram_url', $club->instagram_url ?? '') }}" placeholder="https://instagram.com/..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">𝕏 Twitter/X URL</label>
                                    <input type="url" name="twitter_url" value="{{ old('twitter_url', $club->twitter_url ?? '') }}" placeholder="https://twitter.com/..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">▶️ YouTube URL</label>
                                    <input type="url" name="youtube_url" value="{{ old('youtube_url', $club->youtube_url ?? '') }}" placeholder="https://youtube.com/..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">🧵 Threads URL</label>
                                    <input type="url" name="threads_url" value="{{ old('threads_url', $club->threads_url ?? '') }}" placeholder="https://threads.net/..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">🎵 TikTok URL</label>
                                    <input type="url" name="tiktok_url" value="{{ old('tiktok_url', $club->tiktok_url ?? '') }}" placeholder="https://tiktok.com/..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                </div>
                            </div>
                        </div>

                        <!-- Instagram Automatic Posting Section -->
                        <div class="mb-8 pb-8 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">📱 Instagram Auto-Posting Setup</h3>
                            <p class="text-sm text-gray-600 mb-6">Connect your Instagram Business Account to automatically post event posters to your club's Instagram feed.</p>
                            
                            @php
                                $instagramAccount = $club->instagramAccount;
                                $isConnected = $instagramAccount && $instagramAccount->isTokenValid();
                            @endphp

                            <div class="p-4 rounded-lg border-2 {{ $isConnected ? 'border-green-300 bg-green-50' : 'border-yellow-300 bg-yellow-50' }}">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <p class="text-sm font-semibold {{ $isConnected ? 'text-green-800' : 'text-yellow-800' }}">
                                            {{ $isConnected ? '✅ Connected' : '⚠️ Not Connected' }}
                                        </p>
                                        @if($isConnected)
                                            <p class="text-xs {{ 'text-green-700' }} mt-1">
                                                Instagram Account: <strong>{{ $instagramAccount->instagram_username }}</strong>
                                            </p>
                                            <p class="text-xs {{ 'text-green-700' }} mt-1">
                                                Last post: {{ $instagramAccount->last_post_at ? $instagramAccount->last_post_at->diffForHumans() : 'Never' }}
                                            </p>
                                            @if($instagramAccount->connection_method === 'oauth')
                                                <p class="text-xs {{ 'text-green-700' }} mt-1">
                                                    Connected via: <strong>Instagram OAuth</strong> (Secure)
                                                </p>
                                            @endif
                                        @endif
                                    </div>
                                    @if($isConnected)
                                        <form method="POST" action="{{ route('club-instagram.disconnect') }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded text-sm font-semibold hover:bg-red-600 transition">
                                                🔌 Disconnect
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            @if(!$isConnected)
                                <!-- Two Connection Options -->
                                <div class="mt-6 space-y-6">
                                    <!-- Option 1: OAuth (Easiest) -->
                                    <div class="p-4 bg-gradient-to-r from-blue-50 to-blue-100 border-2 border-blue-300 rounded-lg relative">
                                        <div class="absolute top-2 right-2 bg-yellow-400 text-yellow-800 text-xs font-bold px-3 py-1 rounded-full">
                                            🚀 Coming Soon
                                        </div>
                                        <h4 class="font-bold text-blue-900 mb-2">🔗 Easiest Way: Connect with Instagram</h4>
                                        <p class="text-sm text-blue-800 mb-4">
                                            One-click connection. No need to find account IDs or copy tokens. Just click the button below.
                                        </p>
                                        <p class="text-xs text-blue-700 mb-4 italic">
                                            This feature will be available soon after our Instagram app is reviewed and published by Meta.
                                        </p>
                                        <button disabled class="block w-full px-4 py-3 bg-gray-400 text-white rounded-lg cursor-not-allowed font-semibold text-center opacity-60">
                                            📸 Connect with Instagram (Coming Soon)
                                        </button>
                                    </div>

                                    <!-- Divider -->
                                    <div class="flex items-center">
                                        <div class="flex-1 border-t border-gray-300"></div>
                                        <div class="px-3 text-sm text-gray-500">OR</div>
                                        <div class="flex-1 border-t border-gray-300"></div>
                                    </div>

                                    <!-- Option 2: Manual Token Entry (with Auto-fetch) -->
                                    <div class="p-4 bg-gradient-to-r from-purple-50 to-purple-100 border-2 border-purple-300 rounded-lg">
                                        <h4 class="font-bold text-purple-900 mb-2">🔑 Alternative: Paste Your Access Token</h4>
                                        <p class="text-sm text-purple-800 mb-4">
                                            We'll automatically fetch your account details from the token. Just paste and we handle the rest.
                                        </p>

                                        <form id="tokenForm" class="space-y-4">
                                            @csrf
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-2">Instagram Access Token *</label>
                                                <textarea 
                                                    id="accessTokenInput"
                                                    placeholder="Paste your Instagram access token here..."
                                                    rows="3"
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent font-mono text-xs"
                                                ></textarea>
                                                <p class="text-xs text-gray-500 mt-2">Your token is stored securely and encrypted</p>
                                            </div>

                                            <button type="button" id="fetchBtn" class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                                                🔍 Auto-Fill Account Details
                                            </button>

                                            <!-- Hidden fields for manual form submission -->
                                            <input type="hidden" id="usernameInput" name="instagram_username">
                                            <input type="hidden" id="businessIdInput" name="instagram_business_id">
                                            <input type="hidden" id="tokenInput" name="access_token">

                                            <div id="resultMessage" class="hidden p-3 rounded-lg text-sm"></div>

                                            <button type="submit" id="submitBtn" class="w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold hidden">
                                                ✅ Connect Instagram Account
                                            </button>
                                        </form>

                                        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                            <p class="text-xs text-blue-800 font-semibold mb-2">📚 How to get your access token:</p>
                                            <ol class="text-xs text-blue-700 list-decimal list-inside space-y-1">
                                                <li>Go to <a href="https://developers.facebook.com" target="_blank" class="underline font-semibold">Meta Developers</a></li>
                                                <li>Go to Tools → Graph API Explorer</li>
                                                <li>Generate an access token with:
                                                    <ul class="list-disc list-inside ml-4 mt-1">
                                                        <li>instagram_basic</li>
                                                        <li>instagram_content_publish</li>
                                                    </ul>
                                                </li>
                                                <li>Copy and paste the token above</li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-4">
                            <button type="button" id="saveBtn" class="px-8 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                                Save Changes
                            </button>
                            <a href="{{ route('club-profile.show') }}" class="px-8 py-3 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-semibold">
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

        // Initialize background controls on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Setup vertical range slider
            const bgVertical = document.getElementById('bgVertical');
            if (bgVertical) {
                bgVertical.addEventListener('input', updateBackgroundPosition);
            }

            // Setup drag and drop for background
            setupBackgroundDragAndDrop();

            // Instagram OAuth auto-fetch functionality (optional)
            const fetchBtn = document.getElementById('fetchBtn');
            if (!fetchBtn) {
                console.log('Instagram section not found, skipping');
                return; // Exit if Instagram section not found
            }

            const tokenInput = document.getElementById('accessTokenInput');
            const submitBtn = document.getElementById('submitBtn');
            const resultMessage = document.getElementById('resultMessage');
            const usernameInput = document.getElementById('usernameInput');
            const businessIdInput = document.getElementById('businessIdInput');
            const tokenInputHidden = document.getElementById('tokenInput');
            const tokenForm = document.getElementById('tokenForm');

            fetchBtn.addEventListener('click', async function() {
                const token = tokenInput.value.trim();

                if (!token) {
                    showMessage('Please paste your access token first', 'error');
                    return;
                }

                fetchBtn.disabled = true;
                fetchBtn.innerHTML = '⏳ Fetching...';
                resultMessage.classList.add('hidden');

                try {
                    const response = await fetch('{{ route("instagram.oauth.fetch-account") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ access_token: token })
                    });

                    const data = await response.json();

                    if (data.success) {
                        usernameInput.value = data.instagram_username;
                        businessIdInput.value = data.instagram_business_id;
                        tokenInputHidden.value = token;

                        showMessage('✅ ' + data.message, 'success');
                        submitBtn.classList.remove('hidden');
                    } else {
                        showMessage('❌ ' + data.message, 'error');
                        submitBtn.classList.add('hidden');
                    }
                } catch (error) {
                    showMessage('❌ Error: ' + error.message, 'error');
                    submitBtn.classList.add('hidden');
                } finally {
                    fetchBtn.disabled = false;
                    fetchBtn.innerHTML = '🔍 Auto-Fill Account Details';
                }
            });

            tokenForm.addEventListener('submit', async function(e) {
                if (submitBtn.classList.contains('hidden')) {
                    e.preventDefault();
                    showMessage('❌ Please fetch account details first', 'error');
                    return;
                }
                // Allow form submission
            });

            function showMessage(message, type) {
                resultMessage.classList.remove('hidden');
                resultMessage.textContent = message;
                resultMessage.className = 'p-3 rounded-lg text-sm ' + (type === 'success' ? 'bg-green-100 border border-green-300 text-green-800' : 'bg-red-100 border border-red-300 text-red-800');
            }

            // Allow Enter key in textarea
            tokenInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && e.ctrlKey) {
                    fetchBtn.click();
                }
            });
        });

        // Auto-scroll to validation errors if any
        document.addEventListener('DOMContentLoaded', function() {
            const errorDiv = document.querySelector('[class*="bg-red-100"]');
            if (errorDiv) {
                console.log('Found validation errors, scrolling to top');
                errorDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            // Form submission handling
            const mainForm = document.querySelector('form[method="POST"][enctype="multipart/form-data"]');
            const saveBtn = document.getElementById('saveBtn');
            
            if (mainForm && saveBtn) {
                console.log('✅ Form and button found');
                
                saveBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('✅ Save button clicked');
                    
                    // Get all form fields
                    const nameInput = document.querySelector('input[name="name"]');
                    const emailInput = document.querySelector('input[name="email"]');
                    const categoryInput = document.querySelector('select[name="category"]');
                    const descInput = document.querySelector('textarea[name="description"]');
                    const presidentInput = document.querySelector('input[name="president_name"]');
                    const contactInput = document.querySelector('input[name="president_contact"]');
                    
                    // Log values for debugging
                    console.log('Form values:');
                    console.log('  name:', nameInput?.value);
                    console.log('  email:', emailInput?.value);
                    console.log('  category:', categoryInput?.value);
                    console.log('  description:', descInput?.value);
                    console.log('  president_name:', presidentInput?.value);
                    console.log('  president_contact:', contactInput?.value);
                    
                    // Change button to show loading state
                    saveBtn.disabled = true;
                    saveBtn.textContent = '⏳ Saving...';
                    
                    // Submit the form after a small delay
                    setTimeout(() => {
                        console.log('Submitting form...');
                        mainForm.submit();
                    }, 300);
                });
            } else {
                console.log('❌ Form or button not found');
                console.log('Form:', mainForm);
                console.log('SaveBtn:', saveBtn);
            }
        });
    </script>
</body>
</html>

