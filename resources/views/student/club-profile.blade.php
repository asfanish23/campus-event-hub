<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/UITM_FAVICON.png') }}?v={{ filemtime(public_path('images/UITM_FAVICON.png')) }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $club->name }} | Campus Event Hub</title>
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen bg-gray-50">

    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="hidden w-64 bg-gradient-to-b from-purple-600 to-purple-800 text-white overflow-y-auto flex flex-col">
            <div class="p-6 border-b border-purple-500">
                <h1 class="text-xl font-bold">Campus Event Hub</h1>
                <p class="text-xs text-purple-200">Student Portal</p>
            </div>

            <nav class="flex-1 px-3 py-6 space-y-2 overflow-y-auto">
                <a href="{{ route('student.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>🏠</span>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('student.shop') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>🛍️</span>
                    <span>Shop</span>
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-4 text-xs text-purple-300 uppercase font-semibold tracking-wide">Available Clubs</p>
                </div>
                
                <div class="space-y-2">
                    @forelse($clubs as $c)
                        <a href="{{ route('student.club.show', $c->id) }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ $c->id === $club->id ? 'bg-purple-500 font-medium' : 'hover:bg-purple-500' }} transition text-sm">
                            <span class="flex-shrink-0">
                                @if($c->profile_photo)
                                    <img src="{{ asset('storage/' . $c->profile_photo) }}" alt="{{ $c->name }}" class="w-6 h-6 rounded-full object-cover">
                                @else
                                    <div class="w-6 h-6 rounded-full bg-purple-300 flex items-center justify-center text-xs">{{ substr($c->name, 0, 1) }}</div>
                                @endif
                            </span>
                            <span class="truncate">{{ $c->name }}</span>
                        </a>
                    @empty
                        <p class="px-4 py-3 text-xs text-purple-200">No clubs available</p>
                    @endforelse
                </div>
            </nav>

            <div class="p-3 border-t border-purple-500 space-y-2">
                <a href="{{ route('student.profile.show') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-purple-500 transition text-sm font-semibold">
                    👤 My Profile
                </a>
                <form action="{{ route('logout') }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-semibold">
                        🚪 Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto bg-gray-50">
            <div class="max-w-6xl mx-auto px-6 py-8">

                <!-- Back Button -->
                <a href="{{ route('student.dashboard') }}" class="inline-flex items-center gap-2 text-purple-600 hover:text-purple-800 mb-6 font-semibold">
                    <span>←</span> Back to Dashboard
                </a>

                <!-- Club Header -->
                <div class="bg-white rounded-xl shadow-md mb-8">
                    {{-- Club Banner/Image --}}
                    <div class="relative h-64 bg-gradient-to-br from-purple-400 to-purple-600 overflow-hidden">
                        @if($club->background_photo)
                            <img src="{{ asset('storage/' . $club->background_photo) }}" alt="{{ $club->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-6xl opacity-20">🎉</div>
                        @endif
                    </div>

                    {{-- Club Info --}}
                    <div class="px-8 pb-8 overflow-visible">
                        <div class="flex gap-6 -mt-20 mb-6 items-end relative z-10">
                            {{-- Club Profile Picture --}}
                            <div class="flex-shrink-0">
                                @if($club->profile_photo)
                                    <img src="{{ asset('storage/' . $club->profile_photo) }}" alt="{{ $club->name }}" class="w-32 h-32 rounded-lg border-4 border-white shadow-lg object-cover">
                                @else
                                    <div class="w-32 h-32 rounded-lg border-4 border-white shadow-lg bg-purple-100 flex items-center justify-center text-6xl">
                                        {{ substr($club->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 pb-2">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h1 class="text-4xl font-bold text-gray-800">{{ $club->name }}</h1>
                                    @if(($club->status ?? 'active') === 'active')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Active</span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">Inactive</span>
                                    @endif
                                </div>
                                <p class="text-gray-600 text-lg mt-2">{{ $club->category ?? 'General Club' }}</p>
                                @if(($club->status ?? 'active') !== 'active')
                                    <p class="mt-3 text-sm font-medium text-gray-500">This club is currently inactive.</p>
                                @endif
                            </div>
                        </div>

                        {{-- Club Description --}}
                        @if($club->description)
                            <div class="mb-8">
                                <h2 class="text-xl font-bold text-gray-800 mb-3">About {{ $club->name }}</h2>
                                <p class="text-gray-700 leading-relaxed">{{ $club->description }}</p>
                            </div>
                        @endif

                        {{-- Club Details --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 rounded-lg p-6">
                            @if($club->president_name)
                                <div>
                                    <p class="text-sm text-gray-600 font-semibold uppercase">President</p>
                                    <p class="text-gray-800 font-semibold">{{ $club->president_name }}</p>
                                </div>
                            @endif
                            @if($club->president_contact)
                                <div>
                                    <p class="text-sm text-gray-600 font-semibold uppercase">Contact</p>
                                    <a href="tel:{{ $club->president_contact }}" class="text-purple-600 hover:text-purple-800">{{ $club->president_contact }}</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="mb-6">
                    <div class="flex gap-2 border-b border-gray-300">
                        <button onclick="switchTab('events')" class="tab-btn active px-6 py-4 font-bold text-purple-600 border-b-2 border-purple-600 transition">
                            📅 Events
                        </button>
                        <button onclick="switchTab('merchandise')" class="tab-btn px-6 py-4 font-bold text-gray-600 border-b-2 border-transparent hover:text-gray-800 transition">
                            🛍️ Merchandise
                        </button>
                    </div>
                </div>

                <!-- Events Tab -->
                <div id="events-tab" class="tab-content">
                    <div class="bg-white rounded-xl shadow-md p-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Club Events ({{ $clubEvents->count() }} total)</h2>
                        
                        <!-- Controls Row -->
                        <div class="flex flex-col sm:flex-row gap-4 mb-6">
                            <!-- Search Input -->
                            <input 
                                type="text" 
                                id="searchInput" 
                                placeholder="🔍 Search events..." 
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                            >
                            
                            <!-- Year Filter -->
                            <select 
                                id="yearSelect" 
                                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 bg-white"
                            >
                                <option value="">All Years</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                            
                            <!-- Clear Button -->
                            <button 
                                id="clearFilters" 
                                class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-semibold"
                            >
                                Clear
                            </button>
                        </div>

                        @if($clubEvents->count() > 0)
                            <div id="eventsContainer" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                @foreach($clubEvents as $event)
                                    @php
                                        $eventYear = $event->date->format('Y');
                                    @endphp
                                    <a href="{{ route('student.event.show', $event->id) }}" class="block event-card" data-name="{{ strtolower($event->name) }}" data-year="{{ $eventYear }}">
                                        <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg hover:border-purple-300 transition-all duration-300">
                                            {{-- Event Image --}}
                                            <div class="relative h-48 bg-gradient-to-br from-purple-200 to-purple-400 overflow-hidden">
                                                @if($event->event_image)
                                                    <img src="{{ asset('storage/' . $event->event_image) }}" alt="{{ $event->name }}" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center text-5xl opacity-30">📅</div>
                                                @endif
                                                <div class="absolute top-3 right-3 px-3 py-1 bg-purple-600 text-white rounded-full text-xs font-semibold">
                                                    {{ $event->status }}
                                                </div>
                                            </div>

                                            {{-- Event Details --}}
                                            <div class="p-4">
                                                <h3 class="font-bold text-lg text-gray-800 mb-2 line-clamp-2">{{ $event->name }}</h3>
                                                <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ $event->description }}</p>
                                                
                                                <div class="space-y-2 text-sm text-gray-600 mb-4">
                                                    <div class="flex items-center gap-2">
                                                        <span>📅</span>
                                                        <span>{{ $event->date->format('M d, Y') }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <span>🕐</span>
                                                        <span>{{ $event->start_time->format('H:i') }}</span>
                                                    </div>
                                                    @if($event->location)
                                                        <div class="flex items-center gap-2">
                                                            <span>📍</span>
                                                            <span>{{ $event->location }}</span>
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="pt-4 border-t border-gray-200">
                                                    <span class="text-purple-600 font-semibold text-sm">View Details →</span>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12">
                                <p class="text-3xl mb-3">📭</p>
                                <p class="text-gray-600 text-lg">No events from this club at the moment</p>
                                <p class="text-gray-400 text-sm mt-2">Check back soon!</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Merchandise Tab -->
                <div id="merchandise-tab" class="tab-content hidden">
                    <div class="bg-white rounded-xl shadow-md p-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Merchandise</h2>

                        @if($clubProducts->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach($clubProducts as $product)
                                    <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-all duration-300">
                                        {{-- Product Image --}}
                                        <a href="{{ route('student.shop.show', ['product' => $product->id, 'referrer' => 'club', 'club_id' => $club->id]) }}" class="relative h-48 bg-gray-100 overflow-hidden cursor-pointer block">
                                            @if($product->image)
                                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-5xl opacity-30">📦</div>
                                            @endif
                                            <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-30 transition flex items-center justify-center">
                                                <span class="text-white font-semibold opacity-0 hover:opacity-100">View Details</span>
                                            </div>
                                        </a>

                                        {{-- Product Details --}}
                                        <div class="p-4">
                                            <h3 class="font-bold text-lg text-gray-800 mb-2 line-clamp-2">{{ $product->name }}</h3>
                                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ $product->description }}</p>
                                            
                                            <div class="flex justify-between items-center mb-4">
                                                <div>
                                                    <p class="text-2xl font-bold text-purple-600">RM {{ number_format($product->price, 2) }}</p>
                                                    <p class="text-sm text-gray-500">
                                                        {{ $product->stock ?? 'N/A' }} available
                                                    </p>
                                                </div>
                                            </div>

                                            <a href="{{ route('student.shop.show', ['product' => $product->id, 'referrer' => 'club', 'club_id' => $club->id]) }}" class="block w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold text-sm text-center">
                                                �️ View
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12">
                                <p class="text-3xl mb-3">🛍️</p>
                                <p class="text-gray-600 text-lg">No merchandise available from this club</p>
                                <p class="text-gray-400 text-sm mt-2">Check back soon for new items!</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.add('hidden');
            });

            // Remove active state from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active', 'border-purple-600', 'text-purple-600');
                btn.classList.add('border-transparent', 'text-gray-600');
            });

            // Show selected tab
            document.getElementById(tabName + '-tab').classList.remove('hidden');

            // Add active state to clicked button
            event.target.classList.add('active', 'border-purple-600', 'text-purple-600');
            event.target.classList.remove('border-transparent', 'text-gray-600');
        }

        // Product Modal Functions
        function openProductModal(productId, productName, price, description, stock) {
            const modal = document.getElementById('productModal');
            document.getElementById('modalProductName').textContent = productName;
            document.getElementById('modalProductDescription').textContent = description;
            document.getElementById('modalProductPrice').textContent = 'RM' + price.toFixed(2);
            document.getElementById('modalProductStock').textContent = stock + ' available';
            
            document.getElementById('modalQuantity').value = '1';
            document.getElementById('modalQuantity').max = stock;
            
            document.getElementById('checkoutBtn').onclick = function() {
                checkout(productId);
            };
            
            document.getElementById('addToCartBtn').onclick = function() {
                addToCart(productId, productName, price);
            };
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeProductModal() {
            const modal = document.getElementById('productModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function checkout(productId) {
            const quantity = parseInt(document.getElementById('modalQuantity').value);
            
            // Create a temporary form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("payment.pay") }}';
            
            form.innerHTML = `
                @csrf
                <input type="hidden" name="payment_type" value="merchandise">
                <input type="hidden" name="product_id" value="${productId}">
                <input type="hidden" name="quantity" value="${quantity}">
            `;
            
            document.body.appendChild(form);
            form.submit();
        }

        function addToCart(productId, productName, price) {
            const quantity = parseInt(document.getElementById('modalQuantity').value);
            
            // Get existing cart from localStorage
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            
            // Check if product already exists in cart
            const existingItem = cart.find(item => item.id === productId);
            
            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                cart.push({
                    id: productId,
                    name: productName,
                    price: price,
                    quantity: quantity
                });
            }
            
            localStorage.setItem('cart', JSON.stringify(cart));
            
            closeProductModal();
            
            // Show success message
            showNotification('✓ Added "' + productName + '" to cart! (' + quantity + ' item' + (quantity > 1 ? 's' : '') + ')');
        }

        function showNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg animate-bounce z-50';
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Close modal when clicking outside
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('productModal');
            if(modal) {
                modal.addEventListener('click', function(event) {
                    if (event.target === modal) {
                        closeProductModal();
                    }
                });
            }
        });

        // Event filtering
        const searchInput = document.getElementById('searchInput');
        const yearSelect = document.getElementById('yearSelect');
        const clearFilters = document.getElementById('clearFilters');
        const eventCards = document.querySelectorAll('.event-card');

        function filterEvents() {
            const searchTerm = (searchInput?.value || '').toLowerCase();
            const year = yearSelect?.value;
            let visibleCount = 0;

            eventCards.forEach(card => {
                const eventName = (card.dataset.name || '').toLowerCase();
                const eventYear = card.dataset.year;
                
                const matchesSearch = eventName.includes(searchTerm);
                const matchesYear = !year || eventYear === year;
                const isVisible = matchesSearch && matchesYear;

                card.style.display = isVisible ? 'block' : 'none';
                if (isVisible) visibleCount++;
            });
        }

        searchInput?.addEventListener('input', filterEvents);
        yearSelect?.addEventListener('change', filterEvents);

        clearFilters?.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            if (yearSelect) yearSelect.value = '';
            filterEvents();
        });
    </script>

</body>
</html>
