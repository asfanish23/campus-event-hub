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
                   class="px-6 py-4 text-center border-b-2 border-purple-600 bg-purple-50 text-purple-600 font-semibold">
                    📅 Registrations
                </a>
                <a href="{{ route('student.profile.cart') }}" 
                   class="px-6 py-4 text-center border-b-2 border-gray-200 hover:bg-gray-50 text-gray-600 font-semibold">
                    🛒 Cart
                </a>
                <a href="{{ route('student.profile.orders') }}" 
                   class="px-6 py-4 text-center border-b-2 border-gray-200 hover:bg-gray-50 text-gray-600 font-semibold">
                    📦 Orders
                </a>
            </div>

            {{-- Content --}}
            <div class="p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Registered Events</h2>

                @if($registrations->count() > 0)
                    <div class="space-y-4">
                        @foreach($registrations as $registration)
                            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition">
                                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                    <div class="flex-1">
                                        <h3 class="text-xl font-bold text-gray-900">{{ $registration->event->name }}</h3>
                                        <p class="text-gray-600 mt-1">{{ $registration->event->description }}</p>
                                        
                                        <div class="flex flex-wrap gap-4 mt-4 text-sm text-gray-600">
                                            <span>📅 {{ optional($registration->event->date)->format('M d, Y') ?? 'TBA' }}</span>
                                            <span>🕐 {{ optional($registration->event->start_time)->format('H:i') ?? 'TBA' }}</span>
                                            <span>📍 {{ $registration->event->location ?? 'TBA' }}</span>
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold
                                            @if($registration->status === 'registered')
                                                bg-green-100 text-green-800
                                            @elseif($registration->status === 'attended')
                                                bg-blue-100 text-blue-800
                                            @else
                                                bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst($registration->status) }}
                                        </span>
                                        @if($registration->registered_at)
                                            <p class="text-gray-600 text-sm mt-2">{{ $registration->registered_at->format('M d, Y') }}</p>
                                        @else
                                            <p class="text-gray-600 text-sm mt-2">{{ $registration->created_at->format('M d, Y') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-6">
                        {{ $registrations->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <p class="text-gray-600 text-lg">No event registrations yet</p>
                        <a href="{{ route('student.dashboard') }}" class="mt-4 inline-block px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                            Explore Events
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
