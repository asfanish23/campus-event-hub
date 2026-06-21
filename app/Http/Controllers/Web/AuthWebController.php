<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\User;
use App\Services\ResendEmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class AuthWebController extends Controller
{
    public function __construct(private readonly ResendEmailService $resendEmailService)
    {
    }

    public function showLogin()
    {
        return view('Auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'required|in:admin,super_admin,student',
        ]);

        \Log::info('Login Attempt', ['email' => $request->email, 'role' => $request->role]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            \Log::warning('Login Failed - Invalid Credentials', ['email' => $request->email]);
            return back()->withErrors([
                'email' => 'Invalid credentials',
            ])->withInput();
        }

        $user = Auth::user();
        $user->refresh();

        \Log::info('User Authenticated', ['user_id' => $user->id, 'user_role' => $user->role, 'requested_role' => $request->role]);

        if ($user->role !== $request->role) {
            Auth::logout();
            \Log::warning('Login Failed - Role Mismatch', ['user_id' => $user->id, 'user_role' => $user->role, 'requested_role' => $request->role]);
            return back()->withErrors([
                'role' => 'Role does not match this account',
            ])->withInput();
        }

        // Check if admin is approved
        if ($user->role === 'admin' && $user->admin_status === 'pending') {
            Auth::logout();
            \Log::warning('Login Failed - Admin Pending', ['user_id' => $user->id]);
            return back()->withErrors([
                'email' => 'Your application is pending approval. Please wait for admin to approve your request.',
            ])->withInput();
        }

        if ($user->role === 'admin' && $user->admin_status === 'rejected') {
            Auth::logout();
            \Log::warning('Login Failed - Admin Rejected', ['user_id' => $user->id]);
            return back()->withErrors([
                'email' => 'Your application has been rejected. Please contact super admin for more details.',
            ])->withInput();
        }

        if ($user->role === 'admin') {
            $club = Club::find($user->club_id);

            if (! $club || $club->status !== Club::STATUS_ACTIVE) {
                Auth::logout();
                \Log::warning('Login Failed - Inactive Club', [
                    'user_id' => $user->id,
                    'club_id' => $user->club_id,
                    'club_status' => $club?->status,
                ]);

                return back()->withErrors([
                    'email' => 'Your club is currently inactive. Please contact HEP.',
                ])->withInput();
            }
        }

        \Log::info('Login Success - About to Redirect', ['user_id' => $user->id, 'role' => $user->role]);

        // Regenerate session ID for security
        $request->session()->regenerate();

        // Redirect based on role
        if ($user->role === 'student') {
            \Log::info('Redirecting Student', ['user_id' => $user->id, 'session_id' => $request->session()->getId()]);
            return redirect()->intended(route('student.dashboard'));
        }

        if ($user->role === 'super_admin') {
            \Log::info('Redirecting Super Admin', ['user_id' => $user->id, 'session_id' => $request->session()->getId()]);
            return redirect()->intended(route('super-admin.dashboard'));
        }

        // Club admin (admin role)
        \Log::info('Redirecting Club Admin', ['user_id' => $user->id, 'session_id' => $request->session()->getId()]);
        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        // Clear all authentication data
        Auth::logout();
        
        // Invalidate the session completely
        $request->session()->invalidate();
        
        // Regenerate CSRF token to prevent token reuse
        $request->session()->regenerateToken();
        
        // Clear all cached data
        $request->session()->flush();
        
        // Redirect to login with no-cache headers
        $response = redirect()->route('login')->with('success', 'You have been logged out successfully.');
        
        // Prevent browser caching after logout
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->header('Pragma', 'no-cache');
        $response->header('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');
        
        return $response;
    }

    public function showForgotPassword()
    {
        return view('Auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Allow reset for both admin and student roles
        $user = User::where('email', $request->email)
            ->whereIn('role', ['admin', 'super_admin', 'student'])
            ->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'We could not find an account with that email address.',
            ]);
        }

        try {
            $token = Password::broker()->createToken($user);

            $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);

            $sent = $this->resendEmailService->sendPasswordResetEmail(
                $user->email,
                $user->name ?? 'User',
                $resetUrl
            );

            if (!$sent) {
                return back()->withErrors([
                    'email' => 'Unable to send reset link. Please contact support or try again later.',
                ]);
            }

            return back()->with('status', 'We have emailed your password reset link.');
        } catch (\Exception $e) {
            \Log::error('Failed to send password reset email via Resend.', [
                'email' => $request->email,
                'error_message' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'email' => 'Unable to send reset link. Please contact support or try again later.',
            ]);
        }
    }

    public function showResetPassword(Request $request, $token)
    {
        return view('Auth.reset-password', ['request' => $request, 'token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => bcrypt($password)
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', trans($status));
        }

        return back()->withErrors(['email' => trans($status)]);
    }
}
