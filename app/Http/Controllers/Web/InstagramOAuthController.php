<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\InstagramAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramOAuthController extends Controller
{
    /**
     * Redirect to Instagram OAuth login
     */
    public function redirectToInstagram($clubId)
    {
        try {
            $user = Auth::user();
            Log::info('OAuth redirect started for user', ['user_id' => $user->id, 'club_id' => $clubId]);
            
            // Get the club by ID
            $club = Club::find($clubId);

            if (!$club) {
                Log::warning('Club not found', ['club_id' => $clubId]);
                return redirect()->route('club-profile.edit')->with('error', 'Club not found.');
            }

            // Check if user is admin of this club OR if club has no admin yet (set the current user as admin)
            if ($club->admin_id && $club->admin_id !== $user->id) {
                Log::warning('User cannot manage this club', ['user_id' => $user->id, 'club_id' => $clubId, 'admin_id' => $club->admin_id]);
                return redirect()->route('club-profile.edit')->with('error', 'You do not have permission to manage this club.');
            }

            // If club has no admin, set current user as admin
            if (!$club->admin_id) {
                $club->admin_id = $user->id;
                $club->save();
                Log::info('Set user as club admin', ['user_id' => $user->id, 'club_id' => $clubId]);
            }

            Log::info('Found club for user', ['club_id' => $club->id, 'user_id' => $user->id]);

            // Generate state for security
            $state = bin2hex(random_bytes(16));
            session(['instagram_oauth_state' => $state]);
            session(['instagram_club_id' => $club->id]);

            $clientId = config('services.instagram.app_id');
            
            // Use the exact ngrok URL for redirect URI
            $redirectUri = config('app.url') . '/instagram/oauth/callback';

            Log::info('OAuth config loaded', ['clientId' => $clientId, 'redirectUri' => $redirectUri]);

            if (!$clientId) {
                return redirect()->route('club-profile.edit')->with('error', 'Instagram app not configured. Contact administrator.');
            }

            // Instagram's official OAuth endpoint (for business accounts)
            $url = "https://www.instagram.com/oauth/authorize?" . http_build_query([
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'scope' => 'instagram_business_basic,instagram_business_content_publish',
                'response_type' => 'code',
                'state' => $state,
            ]);

            Log::info('Redirecting to Instagram OAuth', ['url' => $url]);

            return redirect($url);
        } catch (\Exception $e) {
            Log::error('OAuth redirect error: ' . $e->getMessage());
            return redirect()->route('club-profile.edit')->with('error', 'Error starting OAuth: ' . $e->getMessage());
        }
    }

    /**
     * Handle OAuth callback from Instagram
     */
    public function handleCallback(Request $request)
    {
        $code = $request->query('code');
        $state = $request->query('state');

        // Verify state for security
        if ($state !== session('instagram_oauth_state')) {
            Log::error('Instagram OAuth state mismatch', ['expected' => session('instagram_oauth_state'), 'received' => $state]);
            return redirect()->route('club-profile.edit')->with('error', 'Invalid OAuth state. Please try again.');
        }

        if (!$code) {
            Log::warning('Instagram OAuth cancelled or failed');
            return redirect()->route('club-profile.edit')->with('warning', 'Instagram connection cancelled.');
        }

        try {
            // Exchange code for access token
            $clientId = config('services.instagram.app_id');
            $clientSecret = config('services.instagram.app_secret');
            $redirectUri = config('app.url') . '/instagram/oauth/callback';

            $response = Http::post('https://graph.instagram.com/v18.0/oauth/access_token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ]);

            if (!$response->successful()) {
                Log::error('Instagram OAuth token exchange failed', [
                    'status' => $response->status(),
                ]);
                return redirect()->route('club-profile.edit')->with('error', 'Failed to get Instagram token.');
            }

            $data = $response->json();
            $accessToken = $data['access_token'] ?? null;
            $userId = $data['user_id'] ?? null;

            if (!$accessToken || !$userId) {
                return redirect()->route('club-profile.edit')->with('error', 'Could not retrieve Instagram credentials.');
            }

            // Get long-lived token (valid for 60 days instead of 1 hour)
            $longTokenResponse = Http::get('https://graph.instagram.com/v18.0/access_token', [
                'grant_type' => 'ig_refresh_token',
                'access_token' => $accessToken,
            ]);
            if ($longTokenResponse->successful()) {
                $accessToken = $longTokenResponse->json('access_token', $accessToken);
            }
            // Get Instagram business account details
            $accountResponse = Http::get("https://graph.instagram.com/v18.0/{$userId}", [
                'fields' => 'id,username',
                'access_token' => $accessToken,
            ]);
            if (!$accountResponse->successful()) {
                Log::error('Failed to get Instagram account details', ['response' => $accountResponse->json()]);
                return redirect()->route('club-profile.edit')->with('error', 'Failed to get Instagram account details.');
            }
            $accountData = $accountResponse->json();
            $businessId = $accountData['id'] ?? null;
            $username = $accountData['username'] ?? null;

            if (!$businessId || !$username) {
                return redirect()->route('club-profile.edit')->with('error', 'Could not retrieve Instagram account information.');
            }
            // Get club from session
            $clubId = session('instagram_club_id');
            $club = Club::findOrFail($clubId);
            // Save Instagram account
            $instagramAccount = InstagramAccount::firstOrNew(['club_id' => $club->id]);
            $instagramAccount->fill([
                'instagram_username' => $username,
                'instagram_business_id' => $businessId,
                'access_token' => $accessToken,
                'is_active' => true,
                'connection_method' => 'oauth',
            ]);
            $instagramAccount->save();

            Log::info('Club Instagram account connected via OAuth', [
                'club_id' => $club->id,
                'username' => $username,
                'business_id' => $businessId,
            ]);

            // Clear session
            session()->forget(['instagram_oauth_state', 'instagram_club_id']);

            return redirect()->route('club-profile.edit')->with('success', 'Instagram account connected successfully! 🎉');
        } catch (\Exception $e) {
            Log::error('Instagram OAuth exception', ['error' => $e->getMessage()]);
            return redirect()->route('club-profile.edit')->with('error', 'An error occurred during Instagram connection: ' . $e->getMessage());
        }
    }

    /**
     * Auto-fetch Instagram account ID from access token
     * Called via AJAX when user pastes a token
     */
    public function fetchAccountFromToken(Request $request)
    {
        $request->validate([
            'access_token' => 'required|string',
        ]);

        try {
            $token = $request->input('access_token');

            // Get account details from token
            $response = Http::get('https://graph.instagram.com/v18.0/me', [
                'fields' => 'id,username',
                'access_token' => $token,
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid access token. Please check and try again.',
                ], 400);
            }

            $data = $response->json();
            $businessId = $data['id'] ?? null;
            $username = $data['username'] ?? null;

            if (!$businessId || !$username) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not retrieve Instagram account information from token.',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'instagram_username' => $username,
                'instagram_business_id' => $businessId,
                'message' => 'Account details fetched successfully!',
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching Instagram account from token', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
