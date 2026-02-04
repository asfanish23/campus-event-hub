<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Club | Campus Event Hub</title>
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
                <h2 class="text-2xl font-bold text-gray-800">Create Club</h2>
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
                <a href="{{ route('super-admin.manage-clubs') }}" class="text-purple-600 hover:text-purple-800 mb-6 inline-block">← Back to Clubs</a>

                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                        <h3 class="font-semibold mb-2">There were errors in your submission:</h3>
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="max-w-2xl bg-white rounded-lg shadow p-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-6">New Club Information</h3>
                    
                    <form method="POST" action="{{ route('super-admin.clubs.store') }}">
                        @csrf

                        <!-- Club Name -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Club Name *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 @error('name') border-red-500 @enderror"
                                placeholder="Enter club name">
                            @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 @error('email') border-red-500 @enderror"
                                placeholder="Enter club email">
                            @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Category -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Category *</label>
                            <select name="category" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 @error('category') border-red-500 @enderror">
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
                            @error('category') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                            <textarea name="description" rows="5"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600 @error('description') border-red-500 @enderror"
                                placeholder="Enter club description...">{{ old('description') }}</textarea>
                            @error('description') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-4">
                            <button type="submit" class="px-8 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                                Create Club
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
</body>
</html>
