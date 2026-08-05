<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/UITM_FAVICON.png') }}?v={{ filemtime(public_path('images/UITM_FAVICON.png')) }}">
    <title>Orders Management | Campus Event Hub</title>
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
                <a href="{{ route('merchandise.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-purple-500 transition text-sm">
                    <span>👕</span>
                    <span>Merchandise</span>
                </a>
                <a href="{{ route('orders.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-purple-500 hover:bg-purple-400 transition text-sm font-medium">
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
                <h2 class="text-2xl font-bold text-gray-800">Orders</h2>
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

            <!-- Orders Content -->
            <div class="p-8">
                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                <p class="text-gray-600 mb-6">Manage and track merchandise orders</p>

                <!-- Stats -->
                <div class="grid grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-gray-600 text-sm font-semibold mb-2">Total Orders</p>
                        <p class="text-4xl font-bold text-gray-800">{{ $totalOrders }}</p>
                        <p class="text-xs text-gray-500 mt-2">All time</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-gray-600 text-sm font-semibold mb-2">Pending</p>
                        <p class="text-4xl font-bold text-orange-600">{{ $pendingOrders }}</p>
                        <p class="text-xs text-orange-600 mt-2">Needs attention</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-gray-600 text-sm font-semibold mb-2">Completed</p>
                        <p class="text-4xl font-bold text-green-600">{{ $completedOrders }}</p>
                        <p class="text-xs text-gray-500 mt-2">Successfully delivered</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-gray-600 text-sm font-semibold mb-2">Total Revenue</p>
                        <p class="text-4xl font-bold text-purple-600">RM {{ number_format($totalRevenue, 2) }}</p>
                        <p class="text-xs text-gray-500 mt-2">Total sales</p>
                    </div>
                </div>

                <!-- Orders Table -->
                <h3 class="text-xl font-bold text-gray-800 mb-6">Order List</h3>

                <!-- Filters -->
                <div class="mb-6 bg-white rounded-lg shadow p-6">
                    <form method="GET" action="{{ route('orders.index') }}" class="flex gap-4 items-end">
                        <div class="w-40">
                            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="processed" {{ request('status') === 'processed' ? 'selected' : '' }}>Processed</option>
                                <option value="ready" {{ request('status') === 'ready' ? 'selected' : '' }}>Ready</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">Filter</button>
                            <a href="{{ route('orders.index') }}" class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-semibold">Reset</a>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-100 border-b border-gray-200">
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Order ID</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Customer</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Product</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Quantity</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Total</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Payment</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Date</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                                    <td class="py-3 px-4 text-gray-800 font-semibold">{{ $order->order_id }}</td>
                                    <td class="py-3 px-4 text-gray-600">{{ $order->customer_name }}</td>
                                    <td class="py-3 px-4 text-gray-600">{{ $order->product->name }}</td>
                                    <td class="py-3 px-4 text-gray-600">{{ $order->quantity }}</td>
                                    <td class="py-3 px-4 text-gray-600">RM {{ number_format($order->total, 2) }}</td>
                                    <td class="py-3 px-4 text-gray-600">{{ $order->payment_method }}</td>
                                    <td class="py-3 px-4 text-gray-600">{{ $order->date->format('Y-m-d') }}</td>
                                    <td class="py-3 px-4">
                                        @php
                                            $statusColors = [
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'paid' => 'bg-blue-100 text-blue-800',
                                                'processed' => 'bg-indigo-100 text-indigo-800',
                                                'ready' => 'bg-orange-100 text-orange-800',
                                                'completed' => 'bg-green-100 text-green-800',
                                                'cancelled' => 'bg-red-100 text-red-800'
                                            ];
                                            $statusClass = $statusColors[strtolower($order->status)] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-8 text-gray-500">
                                        No orders yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
