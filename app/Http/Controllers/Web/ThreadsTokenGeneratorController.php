<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ThreadsTokenGeneratorController extends Controller
{
    /**
     * One-time utility to generate Threads OAuth credentials for .env.
     *
     * Step 1: Redirect the user to the official Threads OAuth authorization page.
     */
    public function setup()
    {
        $clientId = config('services.threads.app_id');

        if (empty($clientId)) {
            abort(500, 'THREADS_APP_ID is not configured. Add it to .env before using this tool.');
        }

        $state = bin2hex(random_bytes(16));
        session(['threads_setup_state' => $state]);

        $url = 'https://www.threads.net/oauth/authorize?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $this->redirectUri(),
            'scope' => 'threads_basic,threads_content_publish',
            'response_type' => 'code',
            'state' => $state,
        ]);

        return redirect($url);
    }

    /**
     * Handle the OAuth callback and display the generated credentials.
     *
     * Steps 2-6: verify state, exchange code for a short-lived token, exchange
     * for a long-lived token, fetch the user profile, and display the values.
     * Nothing is persisted to the database.
     */
    public function callback(Request $request)
    {
        if ($request->query('state') !== session('threads_setup_state')) {
            abort(400, 'Invalid OAuth state. Start over from /threads/setup.');
        }

        $code = $request->query('code');

        if (!$code) {
            abort(400, 'Authorization was cancelled or failed. Start over from /threads/setup.');
        }

        $clientId = config('services.threads.app_id');
        $clientSecret = config('services.threads.app_secret');

        if (empty($clientId) || empty($clientSecret)) {
            abort(500, 'THREADS_APP_ID and THREADS_APP_SECRET must be configured in .env.');
        }

        // Step 3: Exchange the authorization code for a short-lived access token.
        // Official docs: POST https://graph.threads.net/oauth/access_token
        $exchangeResponse = Http::asForm()->post('https://graph.threads.net/oauth/access_token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUri(),
            'code' => $code,
        ]);

        if (!$exchangeResponse->successful()) {
            Log::error('Threads token exchange failed', ['response' => $exchangeResponse->body()]);
            abort(500, 'Failed to exchange code for access token: ' . $exchangeResponse->body());
        }

        $shortToken = $exchangeResponse->json('access_token');

        if (!$shortToken) {
            abort(500, 'Token exchange response did not include an access token.');
        }

        // Step 4: Exchange the short-lived token for a long-lived token (60 days).
        // Official docs: GET https://graph.threads.net/access_token
        //   ?grant_type=th_exchange_token&client_secret=...&access_token=...
        $longResponse = Http::get('https://graph.threads.net/access_token', [
            'grant_type' => 'th_exchange_token',
            'client_secret' => $clientSecret,
            'access_token' => $shortToken,
        ]);

        if (!$longResponse->successful()) {
            Log::error('Threads long-lived token exchange failed', ['response' => $longResponse->body()]);
            abort(500, 'Failed to exchange for long-lived token: ' . $longResponse->body());
        }

        $accessToken = $longResponse->json('access_token', $shortToken);

        // Step 5: Fetch the authenticated user's profile (id and username).
        $accountResponse = Http::get('https://graph.threads.net/v1.0/me', [
            'fields' => 'id,username',
            'access_token' => $accessToken,
        ]);

        if (!$accountResponse->successful()) {
            Log::error('Threads profile fetch failed', ['response' => $accountResponse->body()]);
            abort(500, 'Failed to fetch Threads profile: ' . $accountResponse->body());
        }

        $userId = $accountResponse->json('id');
        $username = $accountResponse->json('username');

        if (!$userId || !$username) {
            abort(500, 'Profile response did not include id or username.');
        }

        session()->forget('threads_setup_state');

        return view('threads-token.show', compact('accessToken', 'userId', 'username'));
    }

    /**
     * Redirect URI that must be whitelisted in the Threads app dashboard.
     */
    private function redirectUri(): string
    {
        return rtrim(config('app.url'), '/') . '/threads/setup/callback';
    }
}
