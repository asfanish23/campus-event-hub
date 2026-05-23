<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Campus Event Hub</title>
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen overflow-x-hidden bg-[radial-gradient(circle_at_top_left,_rgba(243,232,255,0.85),_transparent_36%),radial-gradient(circle_at_bottom_right,_rgba(237,233,254,0.82),_transparent_36%),linear-gradient(135deg,_#f8fafc_0%,_#ffffff_50%,_#f8fafc_100%)] px-4 py-6 sm:px-6 lg:px-8">

<main class="mx-auto flex min-h-[calc(100vh-3rem)] w-full max-w-6xl items-center justify-center">
    <div class="grid w-full overflow-hidden rounded-[34px] bg-white shadow-[0_28px_90px_rgba(17,24,39,0.14)] ring-1 ring-slate-200/70 lg:min-h-[760px] lg:grid-cols-[1.02fr_0.98fr]">
        <section class="relative flex items-center justify-center overflow-hidden bg-[linear-gradient(160deg,_#4c1d95_0%,_#6d28d9_52%,_#7c3aed_100%)] px-6 py-10 text-white sm:px-10 lg:px-12">
            <div class="pointer-events-none absolute -left-20 top-10 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
            <div class="pointer-events-none absolute bottom-0 right-0 h-72 w-72 rounded-full bg-fuchsia-300/10 blur-3xl"></div>

            <div class="relative w-full max-w-xl space-y-8 text-center lg:text-left">
                <div class="flex justify-center lg:justify-start">
                    <div class="inline-flex items-center justify-center rounded-[26px] bg-white/14 px-5 py-4 shadow-[0_18px_50px_rgba(17,24,39,0.14)] ring-1 ring-white/15 backdrop-blur-md">
                        <img src="{{ asset('images/uitm_logo.png') }}" alt="UiTM Jasin" class="h-12 w-auto object-contain sm:h-14" />
                        <span class="mx-4 h-10 w-px bg-white/20"></span>
                        <img src="{{ asset('images/ceh_logo.png') }}" alt="Campus Event Hub" class="h-12 w-auto object-contain sm:h-14" />
                    </div>
                </div>

                <div class="space-y-4">
                    <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">Campus Event Hub</h1>
                    <p class="mx-auto max-w-lg text-base leading-7 text-white/80 lg:mx-0">UiTM event management for clubs, student engagement, and campus activity coordination.</p>
                </div>

                <ul class="space-y-5 pt-1 text-left">
                    <li class="flex gap-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/14 text-sm">👤</span>
                        <div>
                            <h3 class="text-sm font-semibold text-white">Club Management</h3>
                            <p class="mt-1 text-sm leading-6 text-white/70">Organize events and attendance with less friction.</p>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/14 text-sm">🛡️</span>
                        <div>
                            <h3 class="text-sm font-semibold text-white">HEP Administration</h3>
                            <p class="mt-1 text-sm leading-6 text-white/70">Approve, monitor, and oversee campus clubs.</p>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/14 text-sm">📚</span>
                        <div>
                            <h3 class="text-sm font-semibold text-white">Student Portal</h3>
                            <p class="mt-1 text-sm leading-6 text-white/70">Discover and join events from one dashboard.</p>
                        </div>
                    </li>
                </ul>
            </div>
        </section>

        <section class="flex items-center justify-center bg-white px-5 py-8 sm:px-8 lg:px-10 lg:py-12">
            <div class="w-full max-w-md">
                <div class="overflow-hidden rounded-[30px] bg-white shadow-[0_25px_80px_rgba(17,24,39,0.12)] ring-1 ring-slate-200/70">
                    <div class="bg-gradient-to-br from-purple-600 to-purple-800 px-6 py-6 text-white sm:px-8">
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-purple-100/80">Welcome Back</p>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">Sign in to continue</h2>
                        <p class="mt-2 max-w-sm text-sm leading-6 text-purple-100">Access your dashboard, manage roles, and continue where you left off.</p>
                    </div>

                    <form method="POST" action="{{ route('login.submit') }}" class="space-y-6 px-6 py-7 sm:px-8 sm:py-8">
                        @csrf

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
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="email-section" class="space-y-2 transition-all duration-300">
                            <label class="text-sm font-medium text-slate-700">Email</label>
                            <input name="email" value="{{ old('email') }}" class="h-14 w-full rounded-2xl border border-slate-200 bg-white px-4 text-slate-900 shadow-sm outline-none transition focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10" required>
                            @error('email')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="password-section" class="space-y-2 transition-all duration-300">
                            <div class="flex items-center justify-between gap-4">
                                <label class="text-sm font-medium text-slate-700">Password</label>
                                <a id="forgot-password-link" href="{{ route('password.request') }}" class="text-xs font-medium text-purple-600 hover:text-purple-800">Forgot password?</a>
                            </div>
                            <input type="password" name="password" class="h-14 w-full rounded-2xl border border-slate-200 bg-white px-4 text-slate-900 shadow-sm outline-none transition focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10" required>
                        </div>

                        <button id="signin-btn" class="w-full rounded-2xl bg-gradient-to-r from-purple-600 to-purple-700 py-4 font-semibold text-white shadow-[0_12px_30px_rgba(126,34,206,0.25)] transition hover:from-purple-700 hover:to-purple-800">
                            Sign In
                        </button>
                    </form>

                    <div class="border-t border-slate-200/80 bg-slate-50/80 px-6 py-4 text-sm text-slate-600 sm:px-8">
                        <div id="apply-section">
                            Don't have an account? <a href="{{ route('admin-register') }}" class="font-semibold text-purple-600 hover:text-purple-800">Apply as Admin</a>
                        </div>

                        <div id="student-register-section" class="hidden">
                            Don't have an account? <a href="{{ route('student-register') }}" class="font-semibold text-purple-600 hover:text-purple-800">Register as Student</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

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
