@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="max-w-6xl mx-auto px-4">
        {{-- Breadcrumb --}}
        <div class="mb-6">
            @if(request('referrer') === 'club' && request('club_id'))
                <a href="{{ route('student.club.show', request('club_id')) }}" class="text-purple-600 hover:text-purple-700 font-semibold">
                    ← Back to Club
                </a>
            @else
                <a href="{{ route('student.shop') }}" class="text-purple-600 hover:text-purple-700 font-semibold">
                    ← Back to Shop
                </a>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
            {{-- Product Images --}}
            <div>
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-4">
                    <div class="h-96 bg-gray-200 flex items-center justify-center">
                        @if($product->media->first())
                            <img id="main-image" 
                                 src="{{ asset('storage/' . $product->media->first()->image_path) }}" 
                                 alt="{{ $product->name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <span class="text-gray-500">No image available</span>
                        @endif
                    </div>
                </div>

                {{-- Thumbnail Gallery --}}
                @if($product->media->count() > 1)
                    <div class="flex gap-2 overflow-x-auto">
                        @foreach($product->media as $media)
                            <img src="{{ asset('storage/' . $media->image_path) }}" 
                                 alt="Product image"
                                 class="w-20 h-20 object-cover rounded-lg cursor-pointer hover:opacity-75 transition"
                                 onclick="document.getElementById('main-image').src = '{{ asset('storage/' . $media->image_path) }}'">
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Product Details --}}
            <div>
                {{-- Club Badge --}}
                <div class="inline-block px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm font-semibold mb-4">
                    {{ $product->club->name }}
                </div>

                {{-- Product Name --}}
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $product->name }}</h1>

                {{-- Category --}}
                @if($product->category)
                    <p class="text-gray-600 text-sm mb-4 capitalize">Category: {{ $product->category }}</p>
                @endif

                {{-- Price --}}
                <div class="flex items-baseline gap-2 mb-6">
                    <span class="text-4xl font-bold text-purple-600">RM {{ number_format($product->price, 2) }}</span>
                </div>

                {{-- Stock Status --}}
                <div class="mb-6">
                    @if($product->stock > 0)
                        <span class="inline-block px-4 py-2 bg-green-100 text-green-800 rounded-lg font-semibold">
                            ✓ In Stock ({{ $product->stock }} available)
                        </span>
                    @else
                        <span class="inline-block px-4 py-2 bg-red-100 text-red-800 rounded-lg font-semibold">
                            ✕ Out of Stock
                        </span>
                    @endif
                </div>

                {{-- Description --}}
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Description</h3>
                    <p class="text-gray-600 leading-relaxed">
                        {{ $product->description ?? 'No description available' }}
                    </p>
                </div>

                {{-- Add to Cart Section --}}
                @if($product->stock > 0)
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Quantity</label>
                            <div class="flex items-center border border-gray-300 rounded-lg w-fit">
                                <button type="button" onclick="decreaseQuantity()" 
                                        class="px-4 py-2 text-gray-600 hover:bg-gray-100 transition">−</button>
                                <input type="number" id="quantity" name="quantity" value="1" min="1" max="{{ $product->stock }}"
                                       class="w-16 text-center border-0 focus:ring-0">
                                <button type="button" onclick="increaseQuantity({{ $product->stock }})" 
                                        class="px-4 py-2 text-gray-600 hover:bg-gray-100 transition">+</button>
                            </div>
                        </div>

                        <button type="button" onclick="addProductToCart({{ $product->id }}, '{{ $product->name }}', {{ $product->price }}, '{{ $product->media->first()?->image_path ?? 'default.jpg' }}')" 
                                class="w-full px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold text-lg mb-3">
                            🛒 Add to Cart
                        </button>

                        <form method="POST" action="{{ route('payment.pay') }}" class="w-full">
                            @csrf
                            <input type="hidden" name="payment_type" value="merchandise">
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" id="checkout-quantity" value="1">
                            <button type="submit" onclick="document.getElementById('checkout-quantity').value = document.getElementById('quantity').value;" 
                                    class="w-full px-6 py-3 bg-purple-800 text-white rounded-lg hover:bg-purple-900 transition font-semibold text-lg">
                                💳 Checkout
                            </button>
                        </form>
                    </div>
                @else
                    <button disabled 
                            class="w-full px-6 py-3 bg-gray-300 text-gray-600 rounded-lg cursor-not-allowed font-semibold text-lg">
                        Out of Stock
                    </button>
                @endif

                {{-- Club Info --}}
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">About {{ $product->club->name }}</h3>
                    <p class="text-gray-600 text-sm mb-4">
                        {{ $product->club->description ?? 'No description' }}
                    </p>
                    <a href="{{ route('student.club.show', $product->club) }}" 
                       class="text-purple-600 hover:text-purple-700 font-semibold text-sm">
                        Visit Club Page →
                    </a>
                </div>
            </div>
        </div>

        {{-- Related Products --}}
        @if($relatedProducts->count() > 0)
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">More from {{ $product->club->name }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($relatedProducts as $related)
                        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                            <div class="h-40 bg-gray-200">
                                @if($related->media->first())
                                    <img src="{{ asset('storage/' . $related->media->first()->image_path) }}" 
                                         alt="{{ $related->name }}"
                                         class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div class="p-4">
                                <h4 class="text-sm font-bold text-gray-900 truncate mb-2">{{ $related->name }}</h4>
                                <p class="text-lg font-bold text-purple-600 mb-3">RM {{ number_format($related->price / 100, 2) }}</p>
                                <div class="flex gap-2">
                                    <a href="{{ route('student.shop.show', $related) }}" 
                                       class="flex-1 px-3 py-2 bg-gray-100 text-gray-900 rounded text-center text-sm font-semibold hover:bg-gray-200 transition">
                                        View
                                    </a>
                                    @if($related->stock > 0)
                                        <button onclick="addToCart({{ $related->id }}, '{{ $related->name }}', {{ $related->price }}, '{{ $related->media->first()?->image_path ?? 'default.jpg' }}')" 
                                                class="flex-1 px-3 py-2 bg-purple-600 text-white rounded text-sm font-semibold hover:bg-purple-700 transition">
                                            Add
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    function decreaseQuantity() {
        const qty = document.getElementById('quantity');
        if (qty.value > 1) {
            qty.value = parseInt(qty.value) - 1;
        }
    }

    function increaseQuantity(max) {
        const qty = document.getElementById('quantity');
        if (parseInt(qty.value) < max) {
            qty.value = parseInt(qty.value) + 1;
        }
    }

    function addProductToCart(productId, productName, price, imagePath) {
        const quantity = parseInt(document.getElementById('quantity').value);
        
        // Get existing cart from localStorage
        let cart = JSON.parse(localStorage.getItem('cart')) || [];

        // Check if product already in cart
        const existingItem = cart.find(item => item.id === productId);
        
        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            cart.push({
                id: productId,
                name: productName,
                price: price,
                image: imagePath,
                quantity: quantity
            });
        }

        // Save cart back to localStorage
        localStorage.setItem('cart', JSON.stringify(cart));

        // Show success message
        alert(`${quantity} x ${productName} added to cart!`);
        
        // Reset quantity
        document.getElementById('quantity').value = 1;
    }

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
    }
</script>
@endsection
