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
                   class="px-6 py-4 text-center border-b-2 border-gray-200 hover:bg-gray-50 text-gray-600 font-semibold">
                    🛒 Cart
                </a>
                <a href="{{ route('student.profile.orders') }}" 
                   class="px-6 py-4 text-center border-b-2 border-purple-600 bg-purple-50 text-purple-600 font-semibold">
                    📦 Orders
                </a>
            </div>

            {{-- Content --}}
            <div class="p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Order History</h2>

                @if($orders->count() > 0)
                    <div class="space-y-4">
                        @foreach($orders as $order)
                            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition">
                                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-4 mb-3">
                                            @if($order->product && $order->product->media->count() > 0)
                                                <img src="{{ asset('storage/' . $order->product->media->first()->file_path) }}" 
                                                     alt="{{ $order->product->name }}"
                                                     class="w-16 h-16 rounded-lg object-cover">
                                            @else
                                                <div class="w-16 h-16 rounded-lg bg-gray-200 flex items-center justify-center">
                                                    📦
                                                </div>
                                            @endif
                                            <div>
                                                <h3 class="text-lg font-bold text-gray-900">{{ $order->product->name ?? 'Product' }}</h3>
                                                <p class="text-gray-600 text-sm">Order #{{ $order->id }}</p>
                                            </div>
                                        </div>
                                        
                                        <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                                            <span>🕐 {{ $order->created_at->format('M d, Y H:i') }}</span>
                                            <span>Qty: {{ $order->quantity ?? 1 }}</span>
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <p class="text-2xl font-bold text-purple-600">RM {{ number_format($order->total_price ?? 0, 2) }}</p>
                                        <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold mt-2
                                            @if($order->status === 'completed')
                                                bg-green-100 text-green-800
                                            @elseif($order->status === 'pending')
                                                bg-yellow-100 text-yellow-800
                                            @elseif($order->status === 'cancelled')
                                                bg-red-100 text-red-800
                                            @else
                                                bg-blue-100 text-blue-800
                                            @endif">
                                            {{ ucfirst($order->status ?? 'pending') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-6">
                        {{ $orders->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <p class="text-gray-600 text-lg">No orders yet</p>
                        <a href="{{ route('student.dashboard') }}" class="mt-4 inline-block px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                            Start Shopping
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
