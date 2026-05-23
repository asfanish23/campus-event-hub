<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Campus Event Hub</title>
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen overflow-x-hidden bg-[radial-gradient(circle_at_top_left,_rgba(233,213,255,0.75),_transparent_36%),radial-gradient(circle_at_bottom_right,_rgba(224,231,255,0.75),_transparent_32%),linear-gradient(135deg,_#faf5ff_0%,_#ffffff_48%,_#faf5ff_100%)] flex items-center justify-center px-4 py-6">

<div class="relative w-full max-w-7xl">
    <div class="pointer-events-none absolute -left-24 top-8 h-72 w-72 rounded-full bg-purple-200/35 blur-3xl"></div>
    <div class="pointer-events-none absolute -right-24 bottom-6 h-80 w-80 rounded-full bg-indigo-200/30 blur-3xl"></div>

    <div class="grid grid-cols-1 lg:grid-cols-[0.95fr_1.05fr] items-center gap-8 lg:gap-12">

        {{-- LEFT --}}
        <section class="hidden lg:flex flex-col items-center lg:items-start justify-center">
            <div class="max-w-xl w-full space-y-8">
                <div class="inline-flex items-center justify-center gap-4 rounded-[24px] bg-white/60 px-5 py-4 shadow-[0_10px_40px_rgba(124,58,237,0.08)] backdrop-blur-md ring-1 ring-white/70">
                    <img src="{{ asset('images/uitm_logo.png') }}" alt="UiTM Jasin" class="h-16 w-auto shrink-0 object-contain" />
                    <span class="h-10 w-px bg-purple-200/80"></span>
                    <img src="{{ asset('images/ceh_logo.png') }}" alt="Campus Event Hub" class="h-16 md:h-[78px] w-auto shrink-0 object-contain" />
                </div>

                <div class="max-w-xl">
                    <h1 class="text-5xl font-bold text-slate-900 tracking-tight">Campus Event Hub</h1>
                    <p class="mt-3 text-lg text-slate-600 max-w-lg">UiTM Event Management System for clubs, student engagement, and campus activity management.</p>
                </div>

                <ul class="space-y-4 pt-2 max-w-lg">
                    <li class="flex gap-4">
                        <span class="mt-0.5 text-purple-600">👤</span>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Club Management</h3>
                            <p class="text-sm text-slate-500">Manage events and attendance with less friction.</p>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <span class="mt-0.5 text-purple-600">🛡️</span>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">HEP Administration</h3>
                            <p class="text-sm text-slate-500">Approve, monitor, and oversee campus clubs.</p>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <span class="mt-0.5 text-purple-600">📚</span>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Student Portal</h3>
                            <p class="text-sm text-slate-500">Discover and join events from one dashboard.</p>
                        </div>
                    </li>
                </ul>

                <p class="text-sm text-slate-500">Powered by UiTM Jasin</p>
            </div>
        </section>

        {{-- MOBILE BRANDING --}}
        <section class="lg:hidden mx-auto w-full max-w-md text-center space-y-5">
            <div class="inline-flex items-center justify-center gap-3 rounded-[24px] bg-white/65 px-4 py-3 shadow-[0_10px_40px_rgba(124,58,237,0.08)] backdrop-blur-md ring-1 ring-white/70">
                <img src="{{ asset('images/uitm_logo.png') }}" alt="UiTM Jasin" class="h-12 w-auto object-contain" />
                <img src="{{ asset('images/ceh_logo.png') }}" alt="Campus Event Hub" class="h-14 w-auto object-contain" />
            </div>
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Campus Event Hub</h1>
                <p class="mt-2 text-sm text-slate-600">UiTM Event Management System</p>
            </div>
        </section>

        {{-- RIGHT --}}
        <section class="w-full max-w-lg mx-auto lg:mx-0">
            <div class="rounded-[28px] bg-white/90 shadow-[0_20px_70px_rgba(17,24,39,0.12)] ring-1 ring-white/60 overflow-hidden backdrop-blur-xl">
                <div class="bg-gradient-to-br from-purple-600 to-purple-800 px-8 py-7 text-white">
                    <h2 class="text-2xl md:text-3xl font-bold tracking-tight">Welcome Back</h2>
                    <p class="mt-1 text-sm text-purple-100">Sign in to access your dashboard</p>
                </div>

                <form method="POST" action="{{ route('login.submit') }}" class="px-8 py-8 space-y-6">
                    @csrf

                    {{-- Role --}}
                    <div class="space-y-3">
                        <label class="text-sm font-medium text-slate-700">Select Your Role</label>
                        <div class="grid grid-cols-3 rounded-2xl bg-slate-100 p-1.5 gap-1.5">
                            <label class="role-button flex items-center justify-center rounded-xl bg-white px-3 py-3 text-sm font-medium text-slate-700 cursor-pointer transition-all duration-300" data-role="admin">
                                <input type="radio" name="role" value="admin" checked hidden>
                                Club Admin
                            </label>
                            <label class="role-button flex items-center justify-center rounded-xl bg-white px-3 py-3 text-sm font-medium text-slate-700 cursor-pointer transition-all duration-300" data-role="super_admin">
                                <input type="radio" name="role" value="super_admin" hidden>
                                HEP Officer
                            </label>
                            <label class="role-button flex items-center justify-center rounded-xl bg-white px-3 py-3 text-sm font-medium text-slate-700 cursor-pointer transition-all duration-300" data-role="student">
                                <input type="radio" name="role" value="student" hidden>
                                Student
                            </label>
                        </div>
                        @error('role')
                            <p class="text-red-500 text-xs">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div id="email-section" class="space-y-2 transition-all duration-300">
                        <label class="text-sm font-medium text-slate-700">Email</label>
                        <input name="email" value="{{ old('email') }}" class="h-[52px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-slate-900 shadow-sm outline-none transition focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10" required>
                        @error('email')
                            <p class="text-red-500 text-xs">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div id="password-section" class="space-y-2 transition-all duration-300">
                        <div class="flex justify-between items-center gap-4">
                            <label class="text-sm font-medium text-slate-700">Password</label>
                            <a id="forgot-password-link" href="{{ route('password.request') }}" class="text-xs font-medium text-purple-600 hover:text-purple-800">Forgot password?</a>
                        </div>
                        <input type="password" name="password" class="h-[52px] w-full rounded-2xl border border-slate-200 bg-white px-4 text-slate-900 shadow-sm outline-none transition focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10" required>
                    </div>

                    <button id="signin-btn" class="w-full rounded-2xl bg-gradient-to-r from-purple-600 to-purple-700 py-4 text-white font-semibold shadow-[0_12px_30px_rgba(126,34,206,0.25)] transition hover:from-purple-700 hover:to-purple-800">
                        Sign In
                    </button>
                </form>

                <div class="border-t border-slate-200/80 bg-slate-50/80 px-8 py-4 text-sm text-slate-600">
                    <div id="apply-section">
                        Don't have an account? <a href="{{ route('admin-register') }}" class="font-semibold text-purple-600 hover:text-purple-800">Apply as Admin</a>
                    </div>

                    <div id="student-register-section" class="hidden">
                        Don't have an account? <a href="{{ route('student-register') }}" class="font-semibold text-purple-600 hover:text-purple-800">Register as Student</a>
                    </div>
                </div>
            </div>
        </section>
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
                roleButtons.forEach(b => b.classList.remove('bg-purple-600', 'text-white', 'shadow-[0_8px_24px_rgba(126,34,206,0.25)]'));
                this.classList.add('bg-gradient-to-r', 'from-purple-600', 'to-purple-700', 'text-white', 'shadow-[0_8px_24px_rgba(126,34,206,0.25)]');

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
        roleButtons[0].classList.add('bg-gradient-to-r', 'from-purple-600', 'to-purple-700', 'text-white', 'shadow-[0_8px_24px_rgba(126,34,206,0.25)]');
    });
</script>

</body>
</html>
