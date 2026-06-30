@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="max-w-2xl mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Edit Profile</h1>

            {{-- Photo Upload Section --}}
            <form action="{{ route('student.profile.upload-photo') }}" method="POST" enctype="multipart/form-data" class="mb-8 pb-8 border-b border-gray-200">
                @csrf
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Profile Photo</h2>
                
                <div class="flex flex-col md:flex-row gap-6 items-start">
                    <div>
                        @if($user->profile_photo)
                            <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="{{ $user->name }}" 
                                 class="w-32 h-32 rounded-full object-cover border-4 border-purple-500">
                        @else
                            <div class="w-32 h-32 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white text-4xl font-bold">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        @endif
                    </div>

                    <div class="flex-1">
                        <label class="block text-gray-700 font-semibold mb-3">
                            Choose Photo (JPG, PNG, GIF max 2MB)
                        </label>
                        <input type="file" name="profile_photo" accept="image/*" class="block w-full mb-3">
                        @error('profile_photo')
                            <p class="text-red-600 text-sm mb-3">{{ $message }}</p>
                        @enderror
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                            Upload Photo
                        </button>
                    </div>
                </div>
            </form>

            {{-- Profile Information Form --}}
            @php
                $addressParts = array_map('trim', explode(',', (string) old('address', $user->address ?? '')));
                $addressLine1 = $addressParts[0] ?? '';
                $addressLine2 = count($addressParts) > 1 ? implode(', ', array_slice($addressParts, 1)) : '';
            @endphp
            <form action="{{ route('student.profile.update') }}" method="POST">
                @csrf

                <div class="space-y-6">
                    {{-- Name --}}
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Full Name *</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('name') border-red-500 @enderror"
                               required>
                        @error('name')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Email *</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('email') border-red-500 @enderror"
                               required>
                        @error('email')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Phone</label>
                        <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('phone') border-red-500 @enderror"
                               placeholder="e.g., +60123456789">
                        @error('phone')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Student ID --}}
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Student ID</label>
                        <input type="text" name="student_id" value="{{ old('student_id', $user->student_id) }}" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('student_id') border-red-500 @enderror"
                               placeholder="Your university student ID">
                        @error('student_id')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Faculty --}}
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Faculty *</label>
                        <select name="faculty" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('faculty') border-red-500 @enderror" required>
                            <option value="">Select your faculty</option>
                            @foreach(\App\Models\User::FACULTIES as $code => $name)
                                <option value="{{ $name }}" {{ old('faculty', $user->faculty) === $name ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                        @error('faculty')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Bio --}}
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Bio</label>
                        <textarea name="bio" rows="4" 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('bio') border-red-500 @enderror"
                                  placeholder="Tell us about yourself...">{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Address Line 1 --}}
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Address Line 1</label>
                        <input type="text" name="address_line_1" value="{{ old('address_line_1', $addressLine1) }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('address_line_1') border-red-500 @enderror"
                               placeholder="Street address">
                        @error('address_line_1')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Address Line 2 --}}
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Address Line 2 (Optional)</label>
                        <input type="text" name="address_line_2" value="{{ old('address_line_2', $addressLine2) }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('address_line_2') border-red-500 @enderror"
                               placeholder="Apartment, unit, building (optional)">
                        @error('address_line_2')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- State and City --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">State</label>
                            <select id="state_select" name="state"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('state') border-red-500 @enderror">
                                <option value="">Select your state</option>
                            </select>
                            @error('state')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">City</label>
                            <select id="city_select" name="city"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('city') border-red-500 @enderror"
                                    disabled>
                                <option value="">Select state first</option>
                            </select>
                            @error('city')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Postal Code --}}
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Postal Code</label>
                        <select id="postcode_select" name="postal_code"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('postal_code') border-red-500 @enderror"
                                disabled>
                            <option value="">Select city first</option>
                        </select>
                        @error('postal_code')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <input type="hidden" name="country" value="Malaysia">
                </div>

                <div class="flex gap-4 mt-8">
                    <button type="submit" class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                        💾 Save Changes
                    </button>
                    <a href="{{ route('student.profile.show') }}" class="px-6 py-3 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition font-semibold">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const stateSelect = document.getElementById('state_select');
    const citySelect = document.getElementById('city_select');
    const postcodeSelect = document.getElementById('postcode_select');

    const oldState = @json(old('state', $user->state));
    const oldCity = @json(old('city', $user->city));
    const oldPostcode = @json(old('postal_code', $user->postal_code));

    let malaysiaData = [];

    const normalizeStateName = (name) => {
        if (!name) return '';
        const map = {
            'Wp Kuala Lumpur': 'Kuala Lumpur',
            'Wp Putrajaya': 'Putrajaya',
            'Wp Labuan': 'Labuan',
            'Pulau Pinang': 'Penang',
        };
        return map[name] || name;
    };

    const createOption = (value, label) => {
        const option = document.createElement('option');
        option.value = value;
        option.textContent = label;
        return option;
    };

    const resetSelect = (selectEl, placeholder, disabled = true) => {
        selectEl.innerHTML = '';
        selectEl.appendChild(createOption('', placeholder));
        selectEl.disabled = disabled;
    };

    const findStateByCityAndPostcode = (city, postcode) => {
        if (!city) return null;
        for (const state of malaysiaData) {
            const cityData = (state.city || []).find((item) => item.name === city);
            if (!cityData) continue;
            if (!postcode || (cityData.postcode || []).map(String).includes(String(postcode))) {
                return normalizeStateName(state.name);
            }
        }
        return null;
    };

    const populateStates = (selectedState = '') => {
        const states = malaysiaData
            .map((item) => normalizeStateName(item.name))
            .sort((a, b) => a.localeCompare(b));

        resetSelect(stateSelect, 'Select your state', false);
        states.forEach((state) => stateSelect.appendChild(createOption(state, state)));

        if (selectedState) {
            stateSelect.value = selectedState;
        }
    };

    const populateCities = (stateName, selectedCity = '') => {
        const stateData = malaysiaData.find(
            (item) => normalizeStateName(item.name) === stateName
        );

        if (!stateData) {
            resetSelect(citySelect, 'Select state first', true);
            resetSelect(postcodeSelect, 'Select city first', true);
            return;
        }

        resetSelect(citySelect, 'Select your city', false);
        const cities = (stateData.city || []).map((item) => item.name).sort((a, b) => a.localeCompare(b));
        cities.forEach((city) => citySelect.appendChild(createOption(city, city)));

        if (selectedCity) {
            citySelect.value = selectedCity;
        }
    };

    const populatePostcodes = (stateName, cityName, selectedPostcode = '') => {
        const stateData = malaysiaData.find(
            (item) => normalizeStateName(item.name) === stateName
        );
        const cityData = stateData?.city?.find((item) => item.name === cityName);

        if (!cityData) {
            resetSelect(postcodeSelect, 'Select city first', true);
            return;
        }

        const postcodes = (cityData.postcode || []).map((value) => String(value));
        resetSelect(postcodeSelect, postcodes.length === 1 ? 'Auto-filled postcode' : 'Select postcode', false);

        postcodes.forEach((postcode) => postcodeSelect.appendChild(createOption(postcode, postcode)));

        if (postcodes.length === 1) {
            postcodeSelect.value = postcodes[0];
        } else if (selectedPostcode) {
            postcodeSelect.value = selectedPostcode;
        }
    };

    try {
        const response = await fetch('/data/all.json');
        const payload = await response.json();
        malaysiaData = payload.state || [];

        const inferredState = oldState || findStateByCityAndPostcode(oldCity, oldPostcode) || '';
        populateStates(inferredState);

        if (stateSelect.value) {
            populateCities(stateSelect.value, oldCity || '');
        }

        if (stateSelect.value && citySelect.value) {
            populatePostcodes(stateSelect.value, citySelect.value, oldPostcode || '');
        }

        stateSelect.addEventListener('change', () => {
            populateCities(stateSelect.value, '');
            resetSelect(postcodeSelect, 'Select city first', true);
        });

        citySelect.addEventListener('change', () => {
            populatePostcodes(stateSelect.value, citySelect.value, '');
        });
    } catch (error) {
        resetSelect(stateSelect, 'Unable to load states', true);
        resetSelect(citySelect, 'Unable to load cities', true);
        resetSelect(postcodeSelect, 'Unable to load postcodes', true);
    }
});
</script>
@endsection
