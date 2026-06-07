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
            @php
                $hasVariants = $product->hasVariants();
                $variantData = $product->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'size' => $variant->size,
                        'color' => $variant->color,
                        'price' => (float) $variant->price,
                        'stock' => (int) $variant->stock,
                    ];
                })->values();
            @endphp

            {{-- Product Images --}}
            <div>
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-4">
                    <div class="h-96 bg-gray-200 flex items-center justify-center">
                        @if($product->primary_image_url)
                            <img id="main-image" 
                                 src="{{ $product->primary_image_url }}"
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
                            @if($media->file_type === 'photo' && $media->url)
                                <img src="{{ $media->url }}"
                                     alt="Product image"
                                     class="w-20 h-20 object-cover rounded-lg cursor-pointer hover:opacity-75 transition"
                                     onclick="document.getElementById('main-image').src = '{{ $media->url }}'">
                            @endif
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
                <div class="flex items-baseline gap-2 mb-3">
                    <span id="product-price" class="text-4xl font-bold text-purple-600">
                        RM {{ number_format($hasVariants && $product->variants->first() ? $product->variants->first()->price : $product->price, 2) }}
                    </span>
                </div>

                {{-- Stock Status --}}
                <div class="mb-6">
                    <span id="stock-badge" class="inline-block px-4 py-2 rounded-lg font-semibold {{ ($hasVariants ? ($product->variants->first()?->stock ?? 0) : $product->stock) > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        @if(($hasVariants ? ($product->variants->first()?->stock ?? 0) : $product->stock) > 0)
                            ✓ In Stock (<span id="stock-count">{{ $hasVariants ? ($product->variants->first()?->stock ?? 0) : $product->stock }}</span> available)
                        @else
                            ✕ Out of Stock
                        @endif
                    </span>
                </div>

                @if($hasVariants)
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Choose a Variant</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="variant-selector">
                            @foreach($product->variants as $variant)
                                <button type="button"
                                        class="variant-card text-left border rounded-xl p-4 transition {{ $loop->first ? 'border-purple-600 bg-purple-50' : 'border-gray-200 bg-white hover:border-purple-400' }}"
                                        data-variant-id="{{ $variant->id }}"
                                        data-price="{{ number_format($variant->price, 2, '.', '') }}"
                                        data-stock="{{ $variant->stock }}"
                                        data-size="{{ $variant->size }}"
                                        data-color="{{ $variant->color }}">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $variant->size ?? 'One Size' }} / {{ $variant->color ?? 'Default' }}</p>
                                            <p class="text-sm text-gray-600">Stock: {{ $variant->stock }}</p>
                                        </div>
                                        <span class="text-sm font-bold text-purple-600">RM {{ number_format($variant->price, 2) }}</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Description --}}
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Description</h3>
                    <p class="text-gray-600 leading-relaxed">
                        {{ $product->description ?? 'No description available' }}
                    </p>
                </div>

                {{-- Add to Cart Section --}}
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Quantity</label>
                        <div class="flex items-center border border-gray-300 rounded-lg w-fit">
                            <button type="button" onclick="decreaseQuantity()"
                                    class="px-4 py-2 text-gray-600 hover:bg-gray-100 transition">−</button>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" max="{{ $hasVariants ? ($product->variants->first()?->stock ?? 1) : $product->stock }}"
                                   class="w-16 text-center border-0 focus:ring-0">
                            <button type="button" onclick="increaseQuantity()"
                                    class="px-4 py-2 text-gray-600 hover:bg-gray-100 transition">+</button>
                        </div>
                    </div>

                    <button type="button" onclick="handleAddToCart()"
                            class="w-full px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold text-lg mb-3">
                        🛒 Add to Cart
                    </button>

                    <form method="POST" action="{{ route('payment.pay') }}" class="w-full" id="checkoutForm">
                        @csrf
                        <input type="hidden" name="payment_type" value="merchandise">
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="product_variant_id" id="checkout-variant-id" value="{{ $hasVariants ? ($product->variants->first()?->id ?? '') : '' }}">
                        <input type="hidden" name="quantity" id="checkout-quantity" value="1">
                        <button type="submit" onclick="prepareCheckout();"
                                class="w-full px-6 py-3 bg-purple-800 text-white rounded-lg hover:bg-purple-900 transition font-semibold text-lg">
                            💳 Checkout
                        </button>
                    </form>
                </div>

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
                                @if($related->primary_image_url)
                                    <img src="{{ $related->primary_image_url }}"
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
                                    <a href="{{ route('student.shop.show', $related) }}"
                                       class="flex-1 px-3 py-2 bg-purple-600 text-white rounded text-center text-sm font-semibold hover:bg-purple-700 transition">
                                        Add
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

    const productVariants = @json($variantData);
    let selectedVariant = productVariants.length ? productVariants[0] : null;

    function syncVariantUI() {
        const quantityInput = document.getElementById('quantity');
        const checkoutVariantInput = document.getElementById('checkout-variant-id');
        const stockCount = document.getElementById('stock-count');
        const stockBadge = document.getElementById('stock-badge');
        const priceNode = document.getElementById('product-price');

        if (!selectedVariant) {
            return;
        }

        priceNode.textContent = `RM ${Number(selectedVariant.price).toFixed(2)}`;
        stockCount.textContent = selectedVariant.stock;
        checkoutVariantInput.value = selectedVariant.id;
        quantityInput.max = selectedVariant.stock;

        if (parseInt(quantityInput.value, 10) > selectedVariant.stock) {
            quantityInput.value = selectedVariant.stock > 0 ? 1 : 0;
        }

        stockBadge.className = `inline-block px-4 py-2 rounded-lg font-semibold ${selectedVariant.stock > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
        stockBadge.innerHTML = selectedVariant.stock > 0
            ? `✓ In Stock (<span id="stock-count">${selectedVariant.stock}</span> available)`
            : '✕ Out of Stock';

        document.querySelectorAll('.variant-card').forEach((card) => {
            const isSelected = Number(card.dataset.variantId) === Number(selectedVariant.id);
            card.classList.toggle('border-purple-600', isSelected);
            card.classList.toggle('bg-purple-50', isSelected);
            card.classList.toggle('border-gray-200', !isSelected);
            card.classList.toggle('bg-white', !isSelected);
        });
    }

    function selectVariant(variantId) {
        const next = productVariants.find((variant) => Number(variant.id) === Number(variantId));
        if (!next) {
            return;
        }

        selectedVariant = next;
        syncVariantUI();
    }

    function decreaseQuantity() {
        const qty = document.getElementById('quantity');
        if (qty.value > 1) {
            qty.value = parseInt(qty.value) - 1;
        }
    }

    function increaseQuantity() {
        const qty = document.getElementById('quantity');
        const max = parseInt(qty.max || '1');
        if (parseInt(qty.value) < max) {
            qty.value = parseInt(qty.value) + 1;
        }
    }

    function handleAddToCart() {
        const quantity = parseInt(document.getElementById('quantity').value);
        const productId = {{ $product->id }};
        const productName = @json($product->name);
        const imagePath = @json($product->primary_image_path ?? 'default.jpg');
        const variantId = selectedVariant ? selectedVariant.id : null;
        const price = selectedVariant ? Number(selectedVariant.price) : {{ $product->price }};

        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        const existingItem = cart.find(item => item.id === productId && (item.product_variant_id || null) === variantId);

        if (existingItem) {
            existingItem.quantity += quantity;
            existingItem.price = price;
            existingItem.product_variant_id = variantId;
            existingItem.variant_size = selectedVariant ? selectedVariant.size : null;
            existingItem.variant_color = selectedVariant ? selectedVariant.color : null;
        } else {
            cart.push({
                id: productId,
                name: productName,
                price: price,
                image: imagePath,
                quantity: quantity,
                product_variant_id: variantId,
                variant_size: selectedVariant ? selectedVariant.size : null,
                variant_color: selectedVariant ? selectedVariant.color : null,
            });
        }

        localStorage.setItem('cart', JSON.stringify(cart));
        alert(`${quantity} x ${productName} added to cart!`);
        document.getElementById('quantity').value = 1;
    }

    function prepareCheckout() {
        document.getElementById('checkout-quantity').value = document.getElementById('quantity').value;
        document.getElementById('checkout-variant-id').value = selectedVariant ? selectedVariant.id : '';
    }

    document.querySelectorAll('.variant-card').forEach((card) => {
        card.addEventListener('click', () => selectVariant(card.dataset.variantId));
    });

    syncVariantUI();
</script>
@endsection
