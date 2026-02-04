<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Merchandise Management | Campus Event Hub</title>
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
                <a href="{{ route('instagram.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>📷</span>
                    <span>Instagram</span>
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-4 text-xs text-purple-300 uppercase font-semibold tracking-wide">Manage Shop</p>
                </div>
                <a href="{{ route('merchandise.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-purple-500 hover:bg-purple-400 transition text-sm font-medium">
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
                <h2 class="text-2xl font-bold text-gray-800">Merchandise Management</h2>
                <div class="flex items-center gap-6">
                    <button class="text-gray-600 text-xl">🔔</button>
                    <a href="{{ route('merchandise.create') }}" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                        ➕ Add Product
                    </a>
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

            <!-- Products Content -->
            <div class="p-8">
                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                <p class="text-gray-600 mb-6">Manage products and orders</p>

                <!-- Stats -->
                <div class="grid grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-gray-600 text-sm font-semibold mb-2">Total Products</p>
                        <p class="text-4xl font-bold text-gray-800">{{ $totalProducts }}</p>
                        <p class="text-xs text-gray-500 mt-2">Active listings</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-gray-600 text-sm font-semibold mb-2">Total Sales</p>
                        <p class="text-4xl font-bold text-purple-600">RM {{ number_format($totalSales, 1) }}k</p>
                        <p class="text-xs text-green-600 mt-2">+12% this month</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-gray-600 text-sm font-semibold mb-2">Items Sold</p>
                        <p class="text-4xl font-bold text-gray-800">{{ $itemsSold }}</p>
                        <p class="text-xs text-gray-500 mt-2">All time</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-gray-600 text-sm font-semibold mb-2">Pending Orders</p>
                        <p class="text-4xl font-bold text-orange-600">{{ $pendingOrders }}</p>
                        <p class="text-xs text-orange-600 mt-2">Needs attention</p>
                    </div>
                </div>

                <!-- Products Grid -->
                <h3 class="text-xl font-bold text-gray-800 mb-6">Products</h3>
                <div class="grid grid-cols-4 gap-6">
                    @forelse($products as $product)
                        <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition">
                            <!-- Primary Image (or first media item) -->
                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center relative group cursor-pointer">
                                @if($product->media->count() > 0 && $product->media->first()->file_type === 'photo')
                                    <img src="{{ asset('storage/' . $product->media->first()->file_path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                @elseif($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-gray-400 text-4xl">📦</span>
                                @endif
                                <!-- Media count badge -->
                                @if($product->media->count() > 0)
                                    <div class="absolute top-2 right-2 bg-purple-600 text-white px-2 py-1 rounded text-xs font-semibold">
                                        {{ $product->media->count() }} media
                                    </div>
                                @endif
                            </div>
                            <div class="p-4">
                                <h4 class="font-bold text-gray-800 mb-2">{{ $product->name }}</h4>
                                <p class="text-2xl font-bold text-purple-600 mb-2">RM {{ number_format($product->price, 2) }}</p>
                                <p class="text-sm text-gray-600 mb-4">Stock: {{ $product->stock }}</p>
                                <div class="flex gap-2">
                                    <a href="{{ route('merchandise.show', $product) }}" class="flex-1 px-3 py-2 bg-blue-600 text-white rounded text-sm font-semibold text-center hover:bg-blue-700 transition">
                                        👁️ View
                                    </a>
                                    <a href="{{ route('merchandise.edit', $product) }}" class="flex-1 px-3 py-2 bg-purple-600 text-white rounded text-sm font-semibold text-center hover:bg-purple-700 transition">
                                        ✏️ Edit
                                    </a>
                                    <form method="POST" action="{{ route('merchandise.destroy', $product) }}" class="flex-1" onsubmit="return confirm('Delete this product?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full px-3 py-2 bg-red-600 text-white rounded text-sm font-semibold hover:bg-red-700 transition">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-4 text-center py-12">
                            <p class="text-gray-500 mb-4">No products yet</p>
                            <a href="{{ route('merchandise.create') }}" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                                Create First Product
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </main>
    </div>
</body>
</html>
