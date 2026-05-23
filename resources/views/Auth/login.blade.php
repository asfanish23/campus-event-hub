<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Campus Event Hub</title>

    @vite('resources/css/app.css')
</head>

<body class="min-h-screen bg-slate-100">

<div class="relative min-h-screen overflow-hidden">
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -top-28 -left-24 h-80 w-80 rounded-full bg-purple-200/50 blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 h-80 w-80 rounded-full bg-indigo-200/40 blur-3xl"></div>
    </div>

    <main class="relative z-10 mx-auto flex min-h-screen max-w-6xl items-center px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid w-full overflow-hidden rounded-3xl bg-white shadow-2xl lg:grid-cols-2">

            <section class="relative bg-gradient-to-br from-purple-900 via-purple-700 to-violet-600 px-8 py-12 text-white sm:px-10 lg:px-12">
                <div class="space-y-8">
                    <div class="flex items-center justify-center gap-5 rounded-2xl bg-white/10 px-4 py-4 ring-1 ring-white/20 sm:justify-start">
                        <img src="{{ asset('images/uitm_logo.png') }}" alt="UiTM Logo" class="h-14 w-auto object-contain" />
                        <span class="h-10 w-px bg-white/30"></span>
                        <img src="{{ asset('images/ceh_logo.png') }}" alt="Campus Event Hub" class="h-14 w-auto object-contain" />
                    </div>

                    <div class="space-y-3">
                        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Campus Event Hub</h1>
                        <p class="max-w-md text-sm leading-7 text-purple-100 sm:text-base">
                            One platform for UiTM clubs, HEP officers, and students to manage and join campus events.
                        </p>
                    </div>

                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <span class="mt-1 h-2.5 w-2.5 rounded-full bg-white"></span>
                            <div>
                                <p class="font-semibold">Club Management</p>
                                <p class="text-sm text-purple-100/80">Create events, track attendance, and manage activities.</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-1 h-2.5 w-2.5 rounded-full bg-white"></span>
                            <div>
                                <p class="font-semibold">HEP Oversight</p>
                                <p class="text-sm text-purple-100/80">Review submissions and monitor organization performance.</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-1 h-2.5 w-2.5 rounded-full bg-white"></span>
                            <div>
                                <p class="font-semibold">Student Portal</p>
                                <p class="text-sm text-purple-100/80">Discover and register for events in one place.</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </section>

            <section class="flex items-center justify-center bg-white px-6 py-10 sm:px-8 lg:px-10">
                <div class="w-full max-w-md space-y-6">
                    <div class="space-y-2">
                        <p class="text-xs font-semibold uppercase tracking-widest text-purple-500">Welcome Back</p>
                        <h2 class="text-3xl font-bold tracking-tight text-slate-900">Sign in to continue</h2>
                        <p class="text-sm leading-6 text-slate-500">Access your dashboard and manage campus events seamlessly.</p>
                    </div>

                    <form method="POST" action="{{ route('login.submit') }}" class="space-y-6 rounded-2xl border border-slate-200 bg-slate-50 p-6">

                            @csrf

                            <div class="space-y-3">
                                <label class="text-sm font-semibold text-slate-700">
                                    Select Your Role
                                </label>

                                <div class="grid grid-cols-3 rounded-2xl bg-white p-1.5 shadow-sm">

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

                            <button
                                id="signin-btn"
                                class="h-14 w-full rounded-2xl bg-gradient-to-r from-purple-700 to-purple-600 font-semibold text-white shadow-lg transition-all duration-300 hover:brightness-110"
                            >
                                Sign In
                            </button>

                        </form>

                        <div class="text-sm text-slate-600">

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