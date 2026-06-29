<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Registration | Campus Event Hub</title>
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-purple-50 flex items-center justify-center p-4">

<div class="w-full max-w-2xl">
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">

        <div class="bg-gradient-to-r from-purple-600 to-purple-800 p-6 text-white">
            <h2 class="text-3xl font-bold">Student Registration</h2>
            <p class="text-purple-100">Create your account to discover and attend events</p>
        </div>

        @if (session('success'))
            <div class="m-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('student-register.submit') }}" class="p-6 space-y-5">
            @csrf

            {{-- Full Name --}}
            <div>
                <label class="text-sm font-semibold">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="w-full mt-1 p-3 border rounded-xl @error('name') border-red-500 @enderror" 
                       placeholder="John Doe" required>
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <label class="text-sm font-semibold">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="w-full mt-1 p-3 border rounded-xl @error('email') border-red-500 @enderror" 
                       placeholder="your.email@student.uitm.edu.my" required>
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Phone Number --}}
            <div>
                <label class="text-sm font-semibold">Phone Number</label>
                <input type="tel" name="phone" value="{{ old('phone') }}"
                       class="w-full mt-1 p-3 border rounded-xl @error('phone') border-red-500 @enderror" 
                       placeholder="+60123456789" required>
                @error('phone')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Student ID --}}
            <div>
                <label class="text-sm font-semibold">Student ID</label>
                <input type="text" name="student_id" value="{{ old('student_id') }}"
                       class="w-full mt-1 p-3 border rounded-xl @error('student_id') border-red-500 @enderror" 
                       placeholder="2024XXXXX" required>
                @error('student_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Faculty --}}
            <div>
                <label class="text-sm font-semibold">Faculty</label>
                <select name="faculty" class="w-full mt-1 p-3 border rounded-xl @error('faculty') border-red-500 @enderror" required>
                    <option value="">Select your faculty</option>
                    <option value="Fakulti Perladangan dan Agroteknologi (FPA)" {{ old('faculty') === 'Fakulti Perladangan dan Agroteknologi (FPA)' ? 'selected' : '' }}>
                        Fakulti Perladangan dan Agroteknologi (FPA)
                    </option>
                    <option value="Fakulti Sains Komputer dan Matematik (FSKM)" {{ old('faculty') === 'Fakulti Sains Komputer dan Matematik (FSKM)' ? 'selected' : '' }}>
                        Fakulti Sains Komputer dan Matematik (FSKM)
                    </option>
                </select>
                @error('faculty')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Address Line 1 --}}
            <div>
                <label class="text-sm font-semibold">Address Line 1</label>
                <input type="text" name="address_line_1" value="{{ old('address_line_1') }}"
                      class="w-full mt-1 p-3 border rounded-xl @error('address_line_1') border-red-500 @enderror" 
                       placeholder="Street address" required>
                @error('address_line_1')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Address Line 2 --}}
            <div>
                <label class="text-sm font-semibold">Address Line 2 (Optional)</label>
                <input type="text" name="address_line_2" value="{{ old('address_line_2') }}"
                       class="w-full mt-1 p-3 border rounded-xl @error('address_line_2') border-red-500 @enderror"
                       placeholder="Apartment, unit, building (optional)">
                @error('address_line_2')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- State --}}
            <div>
                <label class="text-sm font-semibold">State</label>
                <select id="state_select" name="state"
                        class="w-full mt-1 p-3 border rounded-xl @error('state') border-red-500 @enderror" required>
                    <option value="">Select your state</option>
                </select>
                @error('state')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- City --}}
            <div>
                <label class="text-sm font-semibold">City</label>
                <select id="city_select" name="city"
                        class="w-full mt-1 p-3 border rounded-xl @error('city') border-red-500 @enderror" required disabled>
                    <option value="">Select state first</option>
                </select>
                @error('city')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Postal Code --}}
            <div>
                <label class="text-sm font-semibold">Postal Code</label>
                <select id="postcode_select" name="postal_code"
                        class="w-full mt-1 p-3 border rounded-xl @error('postal_code') border-red-500 @enderror" required disabled>
                    <option value="">Select city first</option>
                </select>
                @error('postal_code')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div>
                <label class="text-sm font-semibold">Password</label>
                <input type="password" name="password"
                       class="w-full mt-1 p-3 border rounded-xl @error('password') border-red-500 @enderror" 
                       placeholder="Minimum 8 characters" required>
                @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div>
                <label class="text-sm font-semibold">Confirm Password</label>
                <input type="password" name="password_confirmation"
                       class="w-full mt-1 p-3 border rounded-xl" 
                       placeholder="Re-enter your password" required>
            </div>

            <button type="submit" class="w-full py-3 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-all duration-300 font-semibold">
                Create Account
            </button>
        </form>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <p class="text-sm text-gray-600">Already have an account? <a href="{{ route('login') }}" class="text-purple-600 font-semibold hover:text-purple-800">Login here</a></p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const stateSelect = document.getElementById('state_select');
    const citySelect = document.getElementById('city_select');
    const postcodeSelect = document.getElementById('postcode_select');

    const oldState = @json(old('state'));
    const oldCity = @json(old('city'));
    const oldPostcode = @json(old('postal_code'));

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

    const populateStates = () => {
        const states = malaysiaData
            .map((item) => normalizeStateName(item.name))
            .sort((a, b) => a.localeCompare(b));

        resetSelect(stateSelect, 'Select your state', false);
        states.forEach((state) => stateSelect.appendChild(createOption(state, state)));

        if (oldState) {
            stateSelect.value = oldState;
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

        populateStates();

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

</body>
</html>
