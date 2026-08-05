<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\ThreadsAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ThreadsOAuthController extends Controller
{
    /**
     * Redirect to Threads OAuth login
     */
    public function redirectToThreads($club)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return redirect()->route('club-profile.edit')->with('error', 'Unauthorized.');
            }
            Log::info('Threads OAuth redirect started for user', ['user_id' => $user->id, 'club_id' => $club]);
            
            // Get the club by ID
            $club = Club::find($club);

            if (!$club) {
                Log::warning('Club not found', ['club_id' => $club]);
                return redirect()->route('club-profile.edit')->with('error', 'Club not found.');
            }

            // Check if user is admin of this club OR if club has no admin yet (set the current user as admin)
            if ($club->admin_id && $club->admin_id !== $user->id) {
                Log::warning('User cannot manage this club', ['user_id' => $user->id, 'club_id' => $club, 'admin_id' => $club->admin_id]);
                return redirect()->route('club-profile.edit')->with('error', 'You do not have permission to manage this club.');
            }

            // If club has no admin, set current user as admin
            if (!$club->admin_id) {
                $club->admin_id = $user->id;
                $club->save();
                Log::info('Set user as club admin', ['user_id' => $user->id, 'club_id' => $club]);
            }

            Log::info('Found club for user', ['club_id' => $club->id, 'user_id' => $user->id]);

            // Generate state for security
            $state = bin2hex(random_bytes(16));
            session(['threads_oauth_state' => $state]);
            session(['threads_club_id' => $club->id]);

            $clientId = config('services.threads.app_id');

            // Exact redirect URI configured in services.php / .env.
            // Must byte-for-byte match a URI whitelisted in App Dashboard > Threads > Client OAuth Settings.
            $redirectUri = config('services.threads.redirect');

            Log::info('Threads OAuth config loaded', ['clientId' => $clientId, 'redirectUri' => $redirectUri]);

            if (!$clientId) {
                return redirect()->route('club-profile.edit')->with('error', 'Threads app not configured. Contact administrator.');
            }

            if (!$redirectUri) {
                return redirect()->route('club-profile.edit')->with('error', 'Threads redirect URI not configured. Contact administrator.');
            }

            // Threads' official OAuth endpoint
            $url = "https://www.threads.net/oauth/authorize?" . http_build_query([
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'scope' => 'threads_basic,threads_content_publish',
                'response_type' => 'code',
                'state' => $state,
            ]);

            Log::info('Redirecting to Threads OAuth', ['url' => $url]);

            return redirect($url);
        } catch (\Exception $e) {
            Log::error('Threads OAuth redirect error: ' . $e->getMessage());
            return redirect()->route('club-profile.edit')->with('error', 'Error starting OAuth: ' . $e->getMessage());
        }
    }

    /**
     * Handle OAuth callback from Threads
     */
    public function handleCallback(Request $request)
    {
        $code = $request->query('code');
        $state = $request->query('state');

        // Verify state for security
        if ($state !== session('threads_oauth_state')) {
            Log::error('Threads OAuth state mismatch', ['expected' => session('threads_oauth_state'), 'received' => $state]);
            return redirect()->route('club-profile.edit')->with('error', 'Invalid OAuth state. Please try again.');
        }

        if (!$code) {
            Log::warning('Threads OAuth cancelled or failed');
            return redirect()->route('club-profile.edit')->with('warning', 'Threads connection cancelled.');
        }

        try {
            // Exchange code for a short-lived access token.
            // Official docs: POST https://graph.threads.net/oauth/access_token
            $clientId = config('services.threads.app_id');
            $clientSecret = config('services.threads.app_secret');
            $redirectUri = config('services.threads.redirect');

            $response = Http::asForm()->post('https://graph.threads.net/oauth/access_token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ]);

            if (!$response->successful()) {
                Log::error('Threads OAuth token exchange failed', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);
                return redirect()->route('club-profile.edit')->with('error', 'Failed to get Threads token.');
            }

            $data = $response->json();
            $accessToken = $data['access_token'] ?? null;
            $userId = $data['user_id'] ?? null;

            if (!$accessToken || !$userId) {
                return redirect()->route('club-profile.edit')->with('error', 'Could not retrieve Threads credentials.');
            }

            // Exchange the short-lived token for a long-lived token (valid 60 days).
            // Official docs: GET https://graph.threads.net/access_token?grant_type=th_exchange_token&client_secret=...&access_token=...
            $tokenExpiresAt = now()->addDays(60);
            $longTokenResponse = Http::get('https://graph.threads.net/access_token', [
                'grant_type' => 'th_exchange_token',
                'client_secret' => $clientSecret,
                'access_token' => $accessToken,
            ]);
            if ($longTokenResponse->successful()) {
                $accessToken = $longTokenResponse->json('access_token', $accessToken);
                $expiresIn = $longTokenResponse->json('expires_in');
                if ($expiresIn) {
                    $tokenExpiresAt = now()->addSeconds((int) $expiresIn);
                }
            } else {
                Log::warning('Threads long-lived token exchange failed', [
                    'status' => $longTokenResponse->status(),
                    'response' => $longTokenResponse->json(),
                ]);
            }
            // Get Threads account details
            $accountResponse = Http::get("https://graph.threads.net/v1.0/me", [
                'fields' => 'id,username',
                'access_token' => $accessToken,
            ]);
            if (!$accountResponse->successful()) {
                Log::error('Failed to get Threads account details', ['response' => $accountResponse->json()]);
                return redirect()->route('club-profile.edit')->with('error', 'Failed to get Threads account details.');
            }
            $accountData = $accountResponse->json();
            $threadsUserId = $accountData['id'] ?? null;
            $username = $accountData['username'] ?? null;

            if (!$threadsUserId || !$username) {
                return redirect()->route('club-profile.edit')->with('error', 'Could not retrieve Threads account information.');
            }
            // Get club from session
            $clubId = session('threads_club_id');
            $club = Club::findOrFail($clubId);
            // Save Threads account
            $threadsAccount = ThreadsAccount::firstOrNew(['club_id' => $club->id]);
            $threadsAccount->fill([
                'threads_username' => $username,
                'threads_user_id' => $threadsUserId,
                'access_token' => $accessToken,
                'is_active' => true,
                'token_expires_at' => $tokenExpiresAt,
                'connection_method' => 'oauth',
            ]);
            $threadsAccount->save();

            Log::info('Club Threads account connected via OAuth', [
                'club_id' => $club->id,
                'username' => $username,
                'threads_user_id' => $threadsUserId,
            ]);

            // Clear session
            session()->forget(['threads_oauth_state', 'threads_club_id']);

            return redirect()->route('club-profile.edit')->with('success', 'Threads account connected successfully! 🎉');
        } catch (\Exception $e) {
            Log::error('Threads OAuth exception', ['error' => $e->getMessage()]);
            return redirect()->route('club-profile.edit')->with('error', 'An error occurred during Threads connection: ' . $e->getMessage());
        }
    }
}
