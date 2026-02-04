@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="max-w-6xl mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            {{-- Tab Navigation --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-0">
                <a href="{{ route('student.profile.show') }}" 
                   class="px-6 py-4 text-center border-b-2 border-gray-200 hover:bg-gray-50 text-gray-600 font-semibold">
                    📋 Personal Info
                </a>
                <a href="{{ route('student.profile.registrations') }}" 
                   class="px-6 py-4 text-center border-b-2 border-gray-200 hover:bg-gray-50 text-gray-600 font-semibold">
                    📅 Registrations
                </a>
                <a href="{{ route('student.profile.cart') }}" 
                   class="px-6 py-4 text-center border-b-2 border-purple-600 bg-purple-50 text-purple-600 font-semibold">
                    🛒 Cart
                </a>
                <a href="{{ route('student.profile.orders') }}" 
                   class="px-6 py-4 text-center border-b-2 border-gray-200 hover:bg-gray-50 text-gray-600 font-semibold">
                    📦 Orders
                </a>
            </div>

            {{-- Content --}}
            <div class="p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Shopping Cart</h2>

                <div id="cart-container" class="space-y-4">
                    {{-- Cart items will be loaded from localStorage via JavaScript --}}
                </div>

                <div class="mt-8 p-6 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-lg font-semibold text-gray-900">Selected Total:</span>
                        <span id="cart-total" class="text-2xl font-bold text-purple-600">RM 0.00</span>
                    </div>
                    <button id="checkout-btn" class="w-full px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold disabled:opacity-50 disabled:cursor-not-allowed mb-3"
                            disabled>
                        💳 Proceed to Checkout
                    </button>
                    <a href="{{ route('student.shop') }}" class="block w-full px-6 py-3 bg-gray-200 text-gray-900 rounded-lg hover:bg-gray-300 transition font-semibold text-center">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadCart();
});

function loadCart() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const container = document.getElementById('cart-container');
    const totalEl = document.getElementById('cart-total');
    const checkoutBtn = document.getElementById('checkout-btn');

    container.innerHTML = '';

    if (cart.length === 0) {
        container.innerHTML = `
            <div class="text-center py-12">
                <p class="text-gray-600 text-lg mb-4">Your cart is empty</p>
                <a href="{{ route('student.shop') }}" class="inline-block px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                    🛍️ Start Shopping
                </a>
            </div>
        `;
        checkoutBtn.disabled = true;
        return;
    }

    cart.forEach((item, index) => {
        container.innerHTML += `
            <div class="flex items-center gap-4 p-4 border border-gray-200 rounded-lg hover:shadow-lg transition">
                <input type="checkbox" class="cart-checkbox" data-index="${index}" onchange="updateTotal()" 
                       style="width: 20px; height: 20px; cursor: pointer;">
                
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-900">${item.name}</h3>
                    <p class="text-gray-600 text-sm">Size: ${item.size || 'N/A'}</p>
                    <p class="text-purple-600 font-bold mt-2">RM ${(item.price).toFixed(2)} each</p>
                </div>

                <div class="flex flex-col items-center gap-2">
                    <label class="text-gray-600 text-sm font-semibold">Quantity</label>
                    <div class="flex items-center border border-gray-300 rounded-lg">
                        <button type="button" onclick="decreaseQty(${index})" 
                                class="px-2 py-1 text-gray-600 hover:bg-gray-100">−</button>
                        <input type="number" class="w-12 text-center border-0 focus:ring-0" 
                               id="qty-${index}" value="${item.quantity}" min="1"
                               onchange="updateQty(${index}, this.value)">
                        <button type="button" onclick="increaseQty(${index})" 
                                class="px-2 py-1 text-gray-600 hover:bg-gray-100">+</button>
                    </div>
                </div>

                <div class="text-right">
                    <p class="text-gray-600 text-sm mb-2">Subtotal</p>
                    <p class="text-lg font-bold text-purple-600" id="subtotal-${index}">RM ${(item.price * item.quantity).toFixed(2)}</p>
                </div>

                <button type="button" onclick="removeFromCart(${index})" 
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition font-semibold">
                    Remove
                </button>
            </div>
        `;
    });

    updateTotal();
    checkoutBtn.disabled = false;
}

function updateQty(index, newQty) {
    const qty = parseInt(newQty) || 1;
    if (qty < 1) return;
    
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    cart[index].quantity = qty;
    localStorage.setItem('cart', JSON.stringify(cart));
    
    const subtotal = cart[index].price * qty;
    document.getElementById(`subtotal-${index}`).textContent = `RM ${subtotal.toFixed(2)}`;
    updateTotal();
}

function increaseQty(index) {
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    cart[index].quantity += 1;
    localStorage.setItem('cart', JSON.stringify(cart));
    document.getElementById(`qty-${index}`).value = cart[index].quantity;
    
    const subtotal = cart[index].price * cart[index].quantity;
    document.getElementById(`subtotal-${index}`).textContent = `RM ${subtotal.toFixed(2)}`;
    updateTotal();
}

function decreaseQty(index) {
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    if (cart[index].quantity > 1) {
        cart[index].quantity -= 1;
        localStorage.setItem('cart', JSON.stringify(cart));
        document.getElementById(`qty-${index}`).value = cart[index].quantity;
        
        const subtotal = cart[index].price * cart[index].quantity;
        document.getElementById(`subtotal-${index}`).textContent = `RM ${subtotal.toFixed(2)}`;
        updateTotal();
    }
}

function removeFromCart(index) {
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    cart.splice(index, 1);
    localStorage.setItem('cart', JSON.stringify(cart));
    loadCart();
}

function updateTotal() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const checkboxes = document.querySelectorAll('.cart-checkbox');
    const totalEl = document.getElementById('cart-total');
    
    let total = 0;
    checkboxes.forEach((checkbox, index) => {
        if (checkbox.checked && cart[index]) {
            total += cart[index].price * cart[index].quantity;
        }
    });
    
    totalEl.textContent = 'RM ' + total.toFixed(2);
}

function proceedToCheckout() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const checkboxes = document.querySelectorAll('.cart-checkbox:checked');
    
    if (checkboxes.length === 0) {
        alert('Please select at least one item to checkout');
        return;
    }

    // Collect selected items
    const selectedItems = [];
    checkboxes.forEach((checkbox) => {
        const index = parseInt(checkbox.dataset.index);
        selectedItems.push({
            id: cart[index].id,
            quantity: cart[index].quantity,
            price: cart[index].price
        });
    });

    // Store selected items for checkout
    localStorage.setItem('checkoutItems', JSON.stringify(selectedItems));

    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("payment.checkout.multiple") }}';
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
    form.appendChild(csrfInput);

    const itemsInput = document.createElement('input');
    itemsInput.type = 'hidden';
    itemsInput.name = 'items';
    itemsInput.value = JSON.stringify(selectedItems);
    form.appendChild(itemsInput);

    document.body.appendChild(form);
    form.submit();
}

document.getElementById('checkout-btn').onclick = proceedToCheckout;
</script>
@endsection
