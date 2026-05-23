<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Campus Event Hub</title>
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-purple-50 flex items-center justify-center">

<div class="w-full max-w-6xl grid grid-cols-1 lg:grid-cols-2 gap-8 p-6">

    {{-- LEFT --}}
    <div class="hidden lg:flex flex-col justify-center space-y-8">
        <div class="max-w-md mx-auto w-full flex flex-col items-center text-center">
            <div class="inline-flex items-center justify-center gap-5 mb-6 rounded-[28px] bg-white/80 border border-purple-100 px-6 py-4 shadow-sm backdrop-blur-sm">
                <img src="{{ asset('images/uitm_logo.png') }}" alt="UiTM Jasin" class="h-14 md:h-16 w-auto shrink-0 object-contain" />
                <span class="h-12 w-px bg-purple-200/80"></span>
                <img src="{{ asset('images/ceh_logo.png') }}" alt="Campus Event Hub" class="h-16 md:h-20 w-auto shrink-0 object-contain" />
            </div>

            <div>
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 tracking-tight">Campus Event Hub</h1>
                <p class="text-lg md:text-xl text-gray-600 mt-1">UiTM Event Management System</p>
            </div>
        </div>

        <ul class="space-y-4 max-w-md w-full mx-auto text-left">
            <li class="flex gap-3">
                <span class="text-purple-600">👤</span>
                <div>
                    <h3 class="font-semibold">Club Management</h3>
                    <p class="text-sm text-gray-600">Manage events & attendance</p>
                </div>
            </li>
            <li class="flex gap-3">
                <span class="text-purple-600">🛡️</span>
                <div>
                    <h3 class="font-semibold">HEP Administration</h3>
                    <p class="text-sm text-gray-600">Approve & monitor clubs</p>
                </div>
            </li>
            <li class="flex gap-3">
                <span class="text-purple-600">📚</span>
                <div>
                    <h3 class="font-semibold">Student Portal</h3>
                    <p class="text-sm text-gray-600">Discover & attend events</p>
                </div>
            </li>
        </ul>

        <p class="text-sm text-gray-500 text-center max-w-md mx-auto">Powered by UiTM • Secure & Reliable</p>
    </div>

    {{-- RIGHT --}}
    <div class="max-w-md mx-auto w-full bg-white rounded-2xl shadow-xl overflow-hidden">

        <div class="bg-gradient-to-r from-purple-600 to-purple-800 p-6 text-white">
            <h2 class="text-3xl font-bold">Welcome Back</h2>
            <p class="text-purple-100">Sign in to access your dashboard</p>
        </div>

        <form method="POST" action="{{ route('login.submit') }}" class="p-6 space-y-5">
            @csrf

            {{-- Role --}}
            <div>
                <label class="text-sm">Select Your Role</label>
                <div class="grid grid-cols-3 gap-2 mt-2">
                    <label class="border p-3 rounded-xl cursor-pointer text-center transition-all duration-300 role-button text-sm" data-role="admin">
                        <input type="radio" name="role" value="admin" checked hidden>
                        Club Admin
                    </label>
                    <label class="border p-3 rounded-xl cursor-pointer text-center transition-all duration-300 role-button text-sm" data-role="super_admin">
                        <input type="radio" name="role" value="super_admin" hidden>
                        HEP Officer
                    </label>
                    <label class="border p-3 rounded-xl cursor-pointer text-center transition-all duration-300 role-button text-sm" data-role="student">
                        <input type="radio" name="role" value="student" hidden>
                        Student
                    </label>
                </div>
                @error('role')
                    <p class="text-red-500 text-xs">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div id="email-section" class="opacity-100 transition-all duration-500 ease-in-out">
                <label class="text-sm">Email</label>
                <input name="email" value="{{ old('email') }}"
                       class="w-full mt-1 p-3 border rounded-xl" required>
                @error('email')
                    <p class="text-red-500 text-xs">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div id="password-section" class="opacity-100 transition-all duration-500 ease-in-out">
                <div class="flex justify-between items-center">
                    <label class="text-sm">Password</label>
                    <a id="forgot-password-link" href="{{ route('password.request') }}" class="text-xs text-purple-600 hover:text-purple-800">Forgot password?</a>
                </div>
                <input type="password" name="password"
                       class="w-full mt-1 p-3 border rounded-xl" required>
            </div>

            <button id="signin-btn" class="w-full py-3 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-all duration-300">
                Sign In
            </button>
        </form>

        <div id="apply-section" class="px-6 py-4 bg-gray-50 border-t border-gray-200 transition-all duration-300">
            <p class="text-sm text-gray-600">Don't have an account? <a href="{{ route('admin-register') }}" class="text-purple-600 font-semibold hover:text-purple-800">Apply as Admin</a></p>
        </div>

        <div id="student-register-section" class="px-6 py-4 bg-gray-50 border-t border-gray-200 transition-all duration-300 hidden">
            <p class="text-sm text-gray-600">Don't have an account? <a href="{{ route('student-register') }}" class="text-purple-600 font-semibold hover:text-purple-800">Register as Student</a></p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleButtons = document.querySelectorAll('.role-button');
        const emailSection = document.getElementById('email-section');
        const passwordSection = document.getElementById('password-section');
        const signinBtn = document.getElementById('signin-btn');
        const applySection = document.getElementById('apply-section');
        const studentRegisterSection = document.getElementById('student-register-section');
        const forgotPasswordLink = document.getElementById('forgot-password-link');

        roleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const selectedRole = this.getAttribute('data-role');
                
                // Update active state
                roleButtons.forEach(b => b.classList.remove('bg-purple-600', 'text-white', 'border-purple-600'));
                this.classList.add('bg-purple-600', 'text-white', 'border-purple-600');

                // Show form fields with animation
                emailSection.classList.remove('opacity-0', 'pointer-events-none');
                emailSection.classList.add('opacity-100');
                
                passwordSection.classList.remove('opacity-0', 'pointer-events-none');
                passwordSection.classList.add('opacity-100');
                
                signinBtn.classList.remove('opacity-0', 'pointer-events-none');
                signinBtn.classList.add('opacity-100');

                // Show appropriate registration link
                if (selectedRole === 'student') {
                    applySection.classList.add('hidden');
                    studentRegisterSection.classList.remove('hidden');
                    forgotPasswordLink.classList.remove('hidden');
                } else if (selectedRole === 'super_admin') {
                    applySection.classList.add('hidden');
                    studentRegisterSection.classList.add('hidden');
                    forgotPasswordLink.classList.add('hidden');
                } else {
                    applySection.classList.remove('hidden');
                    studentRegisterSection.classList.add('hidden');
                    forgotPasswordLink.classList.remove('hidden');
                }
            });
        });

        // Initialize with Club Admin selected
        roleButtons[0].classList.add('bg-purple-600', 'text-white', 'border-purple-600');
    });
</script>
    </div>
</div>

</body>
</html>
