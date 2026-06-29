@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="max-w-6xl mx-auto px-4">
        {{-- Profile Header --}}
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
            <div class="flex flex-col md:flex-row gap-8 items-start md:items-center">
                {{-- Profile Photo --}}
                <div class="flex flex-col items-center">
                    @if($user->profile_photo)
                        <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="{{ $user->name }}" 
                             class="w-32 h-32 rounded-full object-cover border-4 border-purple-500">
                    @else
                        <div class="w-32 h-32 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white text-4xl font-bold border-4 border-purple-500">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    @endif
                    <a href="{{ route('student.profile.edit') }}" class="mt-4 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm">
                        Edit Profile
                    </a>
                </div>

                {{-- Profile Info --}}
                <div class="flex-1">
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">{{ $user->name }}</h1>
                    <p class="text-gray-600 text-lg mb-4">{{ $user->email }}</p>
                    
                    @if($user->bio)
                        <p class="text-gray-700 mb-4">{{ $user->bio }}</p>
                    @endif

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-200">
                        <div>
                            <p class="text-gray-600 text-sm">Events Registered</p>
                            <p class="text-2xl font-bold text-purple-600">{{ $registrations->count() }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Orders</p>
                            <p class="text-2xl font-bold text-purple-600">{{ $orders->count() }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Phone</p>
                            <p class="font-semibold">{{ $user->phone ?? 'Not set' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Member Since</p>
                            <p class="font-semibold">{{ $user->created_at->format('M Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab Navigation --}}
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-0">
                <a href="{{ route('student.profile.show') }}" 
                   class="px-6 py-4 text-center border-b-2 border-purple-600 bg-purple-50 text-purple-600 font-semibold">
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
                   class="px-6 py-4 text-center border-b-2 border-gray-200 hover:bg-gray-50 text-gray-600 font-semibold">
                    📦 Orders
                </a>
            </div>

            {{-- Personal Info Tab Content --}}
            <div class="p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Personal Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="text-gray-700 font-semibold mb-2 block">Name</label>
                        <p class="text-gray-600 p-3 bg-gray-50 rounded-lg">{{ $user->name }}</p>
                    </div>
                    <div>
                        <label class="text-gray-700 font-semibold mb-2 block">Email</label>
                        <p class="text-gray-600 p-3 bg-gray-50 rounded-lg">{{ $user->email }}</p>
                    </div>
                    <div>
                        <label class="text-gray-700 font-semibold mb-2 block">Phone</label>
                        <p class="text-gray-600 p-3 bg-gray-50 rounded-lg">{{ $user->phone ?? 'Not provided' }}</p>
                    </div>
                    <div>
                        <label class="text-gray-700 font-semibold mb-2 block">Student ID</label>
                        <p class="text-gray-600 p-3 bg-gray-50 rounded-lg">{{ $user->student_id ?? 'Not provided' }}</p>
                    </div>
                    <div>
                        <label class="text-gray-700 font-semibold mb-2 block">Faculty</label>
                        <p class="text-gray-600 p-3 bg-gray-50 rounded-lg">{{ $user->faculty ?? 'Not provided' }}</p>
                    </div>
                    <div>
                        <label class="text-gray-700 font-semibold mb-2 block">City</label>
                        <p class="text-gray-600 p-3 bg-gray-50 rounded-lg">{{ $user->city ?? 'Not provided' }}</p>
                    </div>
                    <div>
                        <label class="text-gray-700 font-semibold mb-2 block">State</label>
                        <p class="text-gray-600 p-3 bg-gray-50 rounded-lg">{{ $user->state ?? 'Not provided' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-gray-700 font-semibold mb-2 block">Address</label>
                        <p class="text-gray-600 p-3 bg-gray-50 rounded-lg">{{ $user->address ?? 'Not provided' }}</p>
                    </div>
                    <div>
                        <label class="text-gray-700 font-semibold mb-2 block">Country</label>
                        <p class="text-gray-600 p-3 bg-gray-50 rounded-lg">{{ $user->country ?? 'Not provided' }}</p>
                    </div>
                    <div>
                        <label class="text-gray-700 font-semibold mb-2 block">Postal Code</label>
                        <p class="text-gray-600 p-3 bg-gray-50 rounded-lg">{{ $user->postal_code ?? 'Not provided' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-gray-700 font-semibold mb-2 block">Bio</label>
                        <p class="text-gray-600 p-3 bg-gray-50 rounded-lg min-h-20">{{ $user->bio ?? 'No bio added' }}</p>
                    </div>
                </div>

                <a href="{{ route('student.profile.edit') }}" 
                   class="inline-block px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                    ✏️ Edit Information
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
