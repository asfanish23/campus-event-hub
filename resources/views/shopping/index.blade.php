@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4">
        <div class="mb-8 flex justify-between items-start">
            <div>
                <h1 class="text-4xl font-bold text-gray-900 mb-2">🛍️ Campus Merchandise</h1>
                <p class="text-gray-600">Explore merchandise from all clubs on campus</p>
            </div>
            <a href="{{ route('student.profile.cart') }}" class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold whitespace-nowrap">
                🛒 View Cart
            </a>
        </div>

        {{-- Filters Section --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <form method="GET" action="{{ route('student.shop') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Search --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
                    <input type="text" name="search" placeholder="Product name..."
                           value="{{ request('search') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>

                {{-- Club Filter --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Club</label>
                    <select name="club" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">All Clubs</option>
                        @foreach($clubs as $club)
                            <option value="{{ $club->id }}" @selected(request('club') == $club->id)>
                                {{ $club->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Category Filter --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                    <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" @selected(request('category') == $category)>
                                {{ ucfirst($category) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Sort --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Sort By</label>
                    <select name="sort" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="latest" @selected(request('sort', 'latest') == 'latest')>Latest</option>
                        <option value="oldest" @selected(request('sort') == 'oldest')>Oldest</option>
                        <option value="price_low" @selected(request('sort') == 'price_low')>Price: Low to High</option>
                        <option value="price_high" @selected(request('sort') == 'price_high')>Price: High to Low</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                        🔍 Filter
                    </button>
                    <a href="{{ route('student.shop') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition font-semibold">
                        ✕ Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Products Grid --}}
        @if($products->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                @foreach($products as $product)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300">
                        {{-- Product Image --}}
                        <div class="relative h-48 bg-gray-200 overflow-hidden">
                            @if($product->media->first())
                                <img src="{{ asset('storage/' . $product->media->first()->image_path) }}" 
                                     alt="{{ $product->name }}"
                                     class="w-full h-full object-cover hover:scale-105 transition duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gray-300">
                                    <span class="text-gray-500 text-sm">No image</span>
                                </div>
                            @endif
                        </div>

                        {{-- Product Info --}}
                        <div class="p-4">
                            {{-- Club Name --}}
                            <p class="text-xs text-purple-600 font-semibold mb-1">{{ $product->club->name }}</p>

                            {{-- Product Name --}}
                            <h3 class="text-lg font-bold text-gray-900 mb-2 truncate">{{ $product->name }}</h3>

                            {{-- Category --}}
                            @if($product->category)
                                <p class="text-xs text-gray-500 mb-2 capitalize">{{ $product->category }}</p>
                            @endif

                            {{-- Description --}}
                            <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $product->description ?? 'No description' }}</p>

                            {{-- Price and Stock --}}
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-2xl font-bold text-purple-600">RM {{ number_format($product->price, 2) }}</span>
                                <span class="text-xs px-2 py-1 rounded-full {{ $product->stock > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $product->stock > 0 ? 'In Stock' : 'Out of Stock' }}
                                </span>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex gap-2">
                                <a href="{{ route('student.shop.show', $product) }}" 
                                   class="flex-1 px-3 py-2 bg-gray-100 text-gray-900 rounded-lg hover:bg-gray-200 transition text-center font-semibold text-sm">
                                    👁️ View Details
                                </a>
                                @if($product->stock > 0)
                                    <button onclick="addToCart({{ $product->id }}, '{{ $product->name }}', {{ $product->price }}, '{{ $product->media->first()?->image_path ?? 'default.jpg' }}')" 
                                            class="flex-1 px-3 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold text-sm">
                                        🛒 Add to Cart
                                    </button>
                                @else
                                    <button disabled 
                                            class="flex-1 px-3 py-2 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed font-semibold text-sm">
                                        Out of Stock
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="flex justify-center">
                {{ $products->links() }}
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <p class="text-gray-600 text-lg mb-4">No products found matching your criteria.</p>
                <a href="{{ route('student.shop') }}" class="inline-block px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                    View All Products
                </a>
            </div>
        @endif
    </div>
</div>

<script>
    function addToCart(productId, productName, price, imagePath) {
        // Get existing cart from localStorage
        let cart = JSON.parse(localStorage.getItem('cart')) || [];

        // Check if product already in cart
        const existingItem = cart.find(item => item.id === productId);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({
                id: productId,
                name: productName,
                price: price,
                image: imagePath,
                quantity: 1
            });
        }

        // Save cart back to localStorage
        localStorage.setItem('cart', JSON.stringify(cart));

        // Show success message
        alert(`${productName} added to cart!`);
        
        // Optionally redirect to cart page
        // window.location.href = '{{ route('student.profile.cart') }}';
    }
</script>
@endsection
