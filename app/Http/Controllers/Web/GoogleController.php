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

use App\Services\GoogleAuthService;

class GoogleController extends Controller
{
    protected $googleAuthService;

    public function __construct(GoogleAuthService $googleAuthService)
    {
        $this->googleAuthService = $googleAuthService;
    }

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
            // Process the Google user info using the shared service
            $result = $this->googleAuthService->processGoogleUser($googleUser, null, true);
            $user = $result['user'];

            // Authenticate and log the user into the system
            Auth::login($user);

            // Regenerate session for security
            request()->session()->regenerate();

            // Redirect to student dashboard
            return redirect()->intended(route('student.dashboard'));

        } catch (Exception $e) {
            Log::error('Error handling Google callback: ' . $e->getMessage());
            return redirect()->route('login')->withErrors([
                'email' => $e->getMessage() ?: 'An unexpected error occurred during Google Sign-In. Please try again.',
            ]);
        }
    }
}
