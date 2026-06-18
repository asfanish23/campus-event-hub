<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Club;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google and log them in.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Exception $e) {
            Log::error('Google Socialite authentication failed: ' . $e->getMessage());
            return redirect()->route('login')->withErrors([
                'email' => 'Google authentication failed or was cancelled.',
            ]);
        }

        try {
            // Find existing user by google_id or email
            $user = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            if ($user) {
                // Check if the user is a student (Google Login is student-only)
                if ($user->role !== 'student') {
                    return redirect()->route('login')->withErrors([
                        'email' => 'Google Sign-In is only available for student accounts.',
                    ]);
                }

                // Update Google authentication info for existing student
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'provider' => 'google',
                ]);
            } else {
                // Register a new user as a student
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'provider' => 'google',
                    'password' => bcrypt(Str::random(24)),
                    'role' => 'student',
                ]);
            }

            // Authenticate and log the user into the system
            Auth::login($user);

            // Regenerate session for security
            request()->session()->regenerate();

            // Redirect to student dashboard
            return redirect()->intended(route('student.dashboard'));

        } catch (Exception $e) {
            Log::error('Error handling Google callback: ' . $e->getMessage());
            return redirect()->route('login')->withErrors([
                'email' => 'An unexpected error occurred during Google Sign-In. Please try again.',
            ]);
        }
    }
}
