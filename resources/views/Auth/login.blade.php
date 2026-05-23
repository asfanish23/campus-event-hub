<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Campus Event Hub</title>

    @vite('resources/css/app.css')
</head>

<body class="min-h-screen bg-white">

<main class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto flex min-h-[calc(100vh-4rem)] w-full max-w-2xl items-center justify-center">
        <div class="w-full rounded-[32px] bg-white/95 shadow-[0_30px_90px_rgba(15,23,42,0.14)] ring-1 ring-slate-200/70 backdrop-blur-xl">
            <div class="px-6 pt-8 sm:px-10 sm:pt-10">
                <div class="flex justify-center">
                    <img src="{{ asset('images/uitm_main_logo.png') }}" alt="UiTM Logo" class="h-16 w-auto object-contain sm:h-20" />
                </div>

                <div class="mt-6 space-y-2 text-center">
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-purple-500">Welcome Back</p>
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Sign in to continue</h1>
                    <p class="mx-auto max-w-md text-sm leading-6 text-slate-500 sm:text-base">
                        Access your dashboard and manage campus events seamlessly.
                    </p>
                </div>
            </div>

            <div class="px-6 py-8 sm:px-10">
                <form method="POST" action="{{ route('login.submit') }}" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_14px_40px_rgba(91,33,182,0.06)] sm:p-7">

                        @csrf

                        <div class="space-y-3">
                            <label class="text-sm font-semibold text-slate-700">Select Your Role</label>
                            <div class="grid grid-cols-3 rounded-2xl bg-slate-50 p-1.5 shadow-sm ring-1 ring-slate-200">
                                <label class="role-button flex cursor-pointer items-center justify-center rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm font-medium text-slate-700 transition-all duration-300 hover:border-purple-300 hover:text-purple-700" data-role="admin">
                                    <input type="radio" name="role" value="admin" checked hidden>
                                    Club Admin
                                </label>
                                <label class="role-button flex cursor-pointer items-center justify-center rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm font-medium text-slate-700 transition-all duration-300 hover:border-purple-300 hover:text-purple-700" data-role="super_admin">
                                    <input type="radio" name="role" value="super_admin" hidden>
                                    HEP Officer
                                </label>
                                <label class="role-button flex cursor-pointer items-center justify-center rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm font-medium text-slate-700 transition-all duration-300 hover:border-purple-300 hover:text-purple-700" data-role="student">
                                    <input type="radio" name="role" value="student" hidden>
                                    Student
                                </label>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-700">Email Address</label>
                            <input type="email" name="email" value="{{ old('email') }}" required class="h-14 w-full rounded-xl border border-gray-300 bg-white px-5 text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/30">
                            @error('email')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-semibold text-slate-700">Password</label>
                                <a id="forgot-password-link" href="{{ route('password.request') }}" class="text-xs font-medium text-purple-600 hover:text-purple-800">Forgot Password?</a>
                            </div>
                            <input type="password" name="password" required class="h-14 w-full rounded-xl border border-gray-300 bg-white px-5 text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/30">
                        </div>

                        <button id="signin-btn" class="h-14 w-full rounded-xl bg-purple-700 font-semibold text-white shadow-lg transition-all duration-300 hover:bg-purple-800">Sign In</button>
                    </form>

                    <div class="px-1 pt-5 text-sm text-slate-600">
                        <div id="apply-section">
                            Don't have an account?
                            <a href="{{ route('admin-register') }}" class="font-semibold text-purple-600 hover:text-purple-800">Apply as Admin</a>
                        </div>

                        <div id="student-register-section" class="hidden">
                            Don't have an account?
                            <a href="{{ route('student-register') }}" class="font-semibold text-purple-600 hover:text-purple-800">Register as Student</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const roleButtons = document.querySelectorAll('.role-button');
    const applySection = document.getElementById('apply-section');
    const studentRegisterSection = document.getElementById('student-register-section');
    const forgotPasswordLink = document.getElementById('forgot-password-link');

    function activateRole(button) {

        roleButtons.forEach(btn => {
            btn.classList.remove('bg-gradient-to-r', 'from-purple-500', 'to-purple-700', 'border-purple-700', 'text-white', 'shadow-lg');
            btn.classList.add('bg-white', 'border-slate-300', 'text-slate-700');
        });

        button.classList.remove('bg-white', 'border-slate-300', 'text-slate-700');
        button.classList.add('bg-gradient-to-r', 'from-purple-500', 'to-purple-700', 'border-purple-700', 'text-white', 'shadow-lg');

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