<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Campus Event Hub</title>

    @vite('resources/css/app.css')
</head>

<body class="min-h-screen overflow-hidden bg-[#f7f7fb]">

<div class="relative min-h-screen overflow-hidden">

    <!-- Background Glow -->
    <div class="absolute inset-0">
        <div class="absolute -top-40 -left-40 h-[500px] w-[500px] rounded-full bg-purple-300/20 blur-3xl"></div>
        <div class="absolute bottom-0 right-0 h-[500px] w-[500px] rounded-full bg-fuchsia-300/20 blur-3xl"></div>
    </div>

    <!-- Main Wrapper -->
    <main class="relative z-10 flex min-h-screen items-center justify-center px-4 py-6 lg:px-10">

        <div class="grid w-full max-w-7xl overflow-hidden rounded-[36px] bg-white shadow-[0_30px_80px_rgba(15,23,42,0.12)] lg:grid-cols-[45%_55%]">

            <!-- LEFT SIDE -->
            <section class="relative flex items-center justify-center overflow-hidden bg-gradient-to-br from-[#4c1d95] via-[#6d28d9] to-[#7c3aed] px-8 py-16 text-white lg:px-16">

                <!-- Glow -->
                <div class="absolute top-0 left-0 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute bottom-0 right-0 h-80 w-80 rounded-full bg-fuchsia-300/10 blur-3xl"></div>

                <div class="relative z-10 max-w-lg">

                    <!-- Logos -->
                    <div class="mb-10 flex items-center gap-5">

                        <div class="flex items-center justify-center rounded-3xl bg-white/10 p-5 backdrop-blur-md ring-1 ring-white/10">
                            <img
                                src="{{ asset('images/uitm_logo.png') }}"
                                alt="UiTM Logo"
                                class="h-20 w-auto object-contain"
                            >
                        </div>

                        <div class="h-14 w-px bg-white/20"></div>

                        <div class="flex items-center justify-center rounded-3xl bg-white/10 p-5 backdrop-blur-md ring-1 ring-white/10">
                            <img
                                src="{{ asset('images/ceh_logo.png') }}"
                                alt="Campus Event Hub"
                                class="h-20 w-auto object-contain"
                            >
                        </div>

                    </div>

                    <!-- Title -->
                    <div class="space-y-5">

                        <h1 class="text-5xl font-bold leading-tight tracking-tight">
                            Campus Event Hub
                        </h1>

                        <p class="max-w-md text-lg leading-8 text-white/75">
                            Modern campus event management for clubs, students, and university administration.
                        </p>

                    </div>

                    <!-- Features -->
                    <div class="mt-14 space-y-7">

                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-lg backdrop-blur-sm">
                                👥
                            </div>

                            <div>
                                <h3 class="text-base font-semibold">
                                    Club Management
                                </h3>

                                <p class="mt-1 text-sm leading-6 text-white/70">
                                    Organize events, attendance, and club activities efficiently.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-lg backdrop-blur-sm">
                                🛡️
                            </div>

                            <div>
                                <h3 class="text-base font-semibold">
                                    HEP Administration
                                </h3>

                                <p class="mt-1 text-sm leading-6 text-white/70">
                                    Approve and oversee student organizations with streamlined workflows.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-lg backdrop-blur-sm">
                                📅
                            </div>

                            <div>
                                <h3 class="text-base font-semibold">
                                    Student Portal
                                </h3>

                                <p class="mt-1 text-sm leading-6 text-white/70">
                                    Discover events and participate through one connected platform.
                                </p>
                            </div>
                        </div>

                    </div>

                </div>

            </section>

            <!-- RIGHT SIDE -->
            <section class="flex items-center justify-center bg-[#fcfcff] px-5 py-10 sm:px-8 lg:px-14">

                <div class="w-full max-w-md">

                    <!-- Card -->
                    <div class="overflow-hidden rounded-[32px] border border-slate-200/60 bg-white shadow-[0_20px_60px_rgba(15,23,42,0.08)]">

                        <!-- Header -->
                        <div class="bg-gradient-to-r from-purple-700 to-purple-600 px-8 py-8 text-white">

                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-purple-100/80">
                                Welcome Back
                            </p>

                            <h2 class="mt-3 text-3xl font-bold tracking-tight">
                                Sign in to continue
                            </h2>

                            <p class="mt-3 text-sm leading-7 text-purple-100/85">
                                Access your dashboard and manage campus events seamlessly.
                            </p>

                        </div>

                        <!-- Form -->
                        <form method="POST" action="{{ route('login.submit') }}" class="space-y-7 px-8 py-8">

                            @csrf

                            <!-- Roles -->
                            <div class="space-y-3">

                                <label class="text-sm font-semibold text-slate-700">
                                    Select Your Role
                                </label>

                                <div class="grid grid-cols-3 rounded-2xl bg-slate-100 p-1.5">

                                    <label class="role-button flex cursor-pointer items-center justify-center rounded-xl px-3 py-3 text-sm font-medium text-slate-600 transition-all duration-300"
                                        data-role="admin">
                                        <input type="radio" name="role" value="admin" checked hidden>
                                        Club Admin
                                    </label>

                                    <label class="role-button flex cursor-pointer items-center justify-center rounded-xl px-3 py-3 text-sm font-medium text-slate-600 transition-all duration-300"
                                        data-role="super_admin">
                                        <input type="radio" name="role" value="super_admin" hidden>
                                        HEP Officer
                                    </label>

                                    <label class="role-button flex cursor-pointer items-center justify-center rounded-xl px-3 py-3 text-sm font-medium text-slate-600 transition-all duration-300"
                                        data-role="student">
                                        <input type="radio" name="role" value="student" hidden>
                                        Student
                                    </label>

                                </div>

                            </div>

                            <!-- Email -->
                            <div class="space-y-2">

                                <label class="text-sm font-semibold text-slate-700">
                                    Email Address
                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    required
                                    class="h-14 w-full rounded-2xl border border-slate-200 bg-white px-5 text-slate-900 outline-none transition-all focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10"
                                >

                                @error('email')
                                    <p class="text-xs text-red-500">{{ $message }}</p>
                                @enderror

                            </div>

                            <!-- Password -->
                            <div class="space-y-2">

                                <div class="flex items-center justify-between">

                                    <label class="text-sm font-semibold text-slate-700">
                                        Password
                                    </label>

                                    <a
                                        id="forgot-password-link"
                                        href="{{ route('password.request') }}"
                                        class="text-xs font-medium text-purple-600 hover:text-purple-800"
                                    >
                                        Forgot Password?
                                    </a>

                                </div>

                                <input
                                    type="password"
                                    name="password"
                                    required
                                    class="h-14 w-full rounded-2xl border border-slate-200 bg-white px-5 text-slate-900 outline-none transition-all focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10"
                                >

                            </div>

                            <!-- Button -->
                            <button
                                id="signin-btn"
                                class="h-14 w-full rounded-2xl bg-gradient-to-r from-purple-700 to-purple-600 font-semibold text-white shadow-[0_12px_30px_rgba(126,34,206,0.25)] transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_18px_40px_rgba(126,34,206,0.35)]"
                            >
                                Sign In
                            </button>

                        </form>

                        <!-- Footer -->
                        <div class="border-t border-slate-100 bg-slate-50 px-8 py-5 text-sm text-slate-600">

                            <div id="apply-section">
                                Don't have an account?
                                <a
                                    href="{{ route('admin-register') }}"
                                    class="font-semibold text-purple-600 hover:text-purple-800"
                                >
                                    Apply as Admin
                                </a>
                            </div>

                            <div id="student-register-section" class="hidden">
                                Don't have an account?
                                <a
                                    href="{{ route('student-register') }}"
                                    class="font-semibold text-purple-600 hover:text-purple-800"
                                >
                                    Register as Student
                                </a>
                            </div>

                        </div>

                    </div>

                </div>

            </section>

        </div>

    </main>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const roleButtons = document.querySelectorAll('.role-button');
    const applySection = document.getElementById('apply-section');
    const studentRegisterSection = document.getElementById('student-register-section');
    const forgotPasswordLink = document.getElementById('forgot-password-link');

    function activateRole(button) {

        roleButtons.forEach(btn => {
            btn.classList.remove(
                'bg-gradient-to-r',
                'from-purple-700',
                'to-purple-600',
                'text-white',
                'shadow-lg'
            );
        });

        button.classList.add(
            'bg-gradient-to-r',
            'from-purple-700',
            'to-purple-600',
            'text-white',
            'shadow-lg'
        );

        const role = button.dataset.role;

        if (role === 'student') {

            applySection.classList.add('hidden');
            studentRegisterSection.classList.remove('hidden');
            forgotPasswordLink.classList.remove('hidden');

        } else if (role === 'super_admin') {

            applySection.classList.add('hidden');
            studentRegisterSection.classList.add('hidden');
            forgotPasswordLink.classList.add('hidden');

        } else {

            applySection.classList.remove('hidden');
            studentRegisterSection.classList.add('hidden');
            forgotPasswordLink.classList.remove('hidden');

        }
    }

    roleButtons.forEach(button => {

        button.addEventListener('click', () => {
            activateRole(button);
        });

    });

    activateRole(roleButtons[0]);

});
</script>

</body>
</html>