<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/UITM_FAVICON.png') }}?v={{ filemtime(public_path('images/UITM_FAVICON.png')) }}">
    <title>{{ $product->name }} | Campus Event Hub</title>
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
                    <span>📱</span>
                    <span>Social Media</span>
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
                <div>
                    <a href="{{ route('merchandise.index') }}" class="text-purple-600 hover:text-purple-700 text-sm mb-1">← Back to Merchandise</a>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $product->name }}</h2>
                </div>
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

            <!-- Product Details -->
            <div class="p-8 max-w-5xl">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <!-- Product Gallery -->
                    <div class="grid grid-cols-2 gap-8 p-8">
                        <!-- Left: Primary Image -->
                        <div>
                            <div class="bg-gray-200 rounded-lg overflow-hidden flex items-center justify-center mb-4" style="aspect-ratio: 1;">
                                @if($product->media->count() > 0 && $product->media->first()->file_type === 'photo')
                                    <img id="mainImage" src="{{ asset('storage/' . $product->media->first()->file_path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-gray-400 text-6xl">📦</span>
                                @endif
                            </div>

                            <!-- Thumbnail Gallery -->
                            @if($product->media->count() > 1)
                                <div class="grid grid-cols-4 gap-2">
                                    @foreach($product->media as $media)
                                        <button onclick="changeMainImage(this)" class="rounded-lg overflow-hidden bg-gray-200 flex items-center justify-center aspect-square border-2 border-transparent hover:border-purple-600 transition" data-src="@if($media->file_type === 'photo'){{ asset('storage/' . $media->file_path) }}@endif" data-type="{{ $media->file_type }}">
                                            @if($media->file_type === 'photo')
                                                <img src="{{ asset('storage/' . $media->file_path) }}" alt="Thumbnail" class="w-full h-full object-cover">
                                            @else
                                                <span class="text-2xl">🎥</span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Right: Product Info -->
                        <div>
                            <!-- Price -->
                            <div class="mb-6">
                                <p class="text-gray-600 text-sm mb-2">Price</p>
                                <p class="text-4xl font-bold text-purple-600">RM {{ number_format($product->price, 2) }}</p>
                            </div>

                            <!-- Category & Stock -->
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div>
                                    <p class="text-gray-600 text-sm mb-1">Category</p>
                                    <p class="font-semibold text-gray-800">{{ $product->category }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600 text-sm mb-1">Stock Available</p>
                                    <p class="text-2xl font-bold {{ $product->stock > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $product->stock }}
                                    </p>
                                </div>
                            </div>

                            @if($product->product_type === 'variant' && $product->variants->isNotEmpty())
                                <div class="mb-8">
                                    <p class="text-gray-600 text-sm font-semibold mb-3">Variants</p>
                                    <div class="overflow-hidden border border-gray-200 rounded-lg">
                                        <table class="w-full text-left text-sm">
                                            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                                                <tr>
                                                    <th class="px-4 py-3">Size</th>
                                                    <th class="px-4 py-3">Color</th>
                                                    <th class="px-4 py-3">Price</th>
                                                    <th class="px-4 py-3">Stock</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($product->variants as $variant)
                                                    <tr class="border-t border-gray-200">
                                                        <td class="px-4 py-3">{{ $variant->size ?? 'N/A' }}</td>
                                                        <td class="px-4 py-3">{{ $variant->color ?? 'N/A' }}</td>
                                                        <td class="px-4 py-3">RM {{ number_format($variant->price, 2) }}</td>
                                                        <td class="px-4 py-3">{{ $variant->stock }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            <!-- Description -->
                            @if($product->description)
                                <div class="mb-8">
                                    <p class="text-gray-600 text-sm font-semibold mb-2">Description</p>
                                    <p class="text-gray-700 leading-relaxed">{{ $product->description }}</p>
                                </div>
                            @endif

                            <!-- Media Count -->
                            @if($product->media->count() > 0)
                                <div class="mb-8">
                                    <p class="text-gray-600 text-sm font-semibold mb-2">Media</p>
                                    <p class="text-gray-700">{{ $product->media->count() }} file(s) • 
                                        <span class="text-sm">
                                            {{ $product->media->where('file_type', 'photo')->count() }} photo(s), 
                                            {{ $product->media->where('file_type', 'video')->count() }} video(s)
                                        </span>
                                    </p>
                                </div>
                            @endif

                            <!-- Dates -->
                            <div class="grid grid-cols-2 gap-4 mb-8 text-sm text-gray-600">
                                <div>
                                    <p class="text-gray-500">Created</p>
                                    <p>{{ $product->created_at->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Last Updated</p>
                                    <p>{{ $product->updated_at->format('M d, Y') }}</p>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-3">
                                <a href="{{ route('merchandise.edit', $product) }}" class="flex-1 px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold text-center">
                                    ✏️ Edit Product
                                </a>
                                <form method="POST" action="{{ route('merchandise.destroy', $product) }}" class="flex-1" onsubmit="return confirm('Delete this product?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold">
                                        🗑️ Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Full Media Gallery -->
                    @if($product->media->count() > 1)
                        <div class="border-t border-gray-200 p-8">
                            <h3 class="text-xl font-bold text-gray-800 mb-6">All Media</h3>
                            <div class="grid grid-cols-6 gap-4">
                                @foreach($product->media as $media)
                                    <div class="bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center aspect-square">
                                        @if($media->file_type === 'photo')
                                            <img src="{{ asset('storage/' . $media->file_path) }}" alt="Product media" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-4xl">🎥</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    <script>
        function changeMainImage(button) {
            const src = button.getAttribute('data-src');
            const type = button.getAttribute('data-type');
            const mainImage = document.getElementById('mainImage');

            if (type === 'photo' && src) {
                mainImage.src = src;
                mainImage.style.display = 'block';
            }

            // Update active thumbnail
            document.querySelectorAll('[onclick="changeMainImage(this)"]').forEach(btn => {
                btn.classList.remove('border-purple-600');
                btn.classList.add('border-transparent');
            });
            button.classList.add('border-purple-600');
            button.classList.remove('border-transparent');
        }
    </script>
</body>
</html>
