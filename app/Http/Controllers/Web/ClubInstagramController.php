<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\InstagramAccount;
use App\Services\ClubActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClubInstagramController extends Controller
{
    private ClubActivityService $clubActivityService;

    public function __construct(ClubActivityService $clubActivityService)
    {
        $this->clubActivityService = $clubActivityService;
    }

    /**
     * Store Instagram credentials for a club
     */
    public function storeCredentials(Request $request)
    {
        $user = Auth::user();
        $club = Club::where('admin_id', $user->id)->first();

        if (!$club) {
            return back()->with('error', 'You do not have a club to manage.');
        }

        $this->clubActivityService->ensureClubIsActive($club);

        $validated = $request->validate([
            'instagram_username' => 'required|string|max:255',
            'instagram_business_id' => 'required|string|max:255',
            'access_token' => 'required|string',
        ]);

        try {
            // Find or create Instagram account for this club
            $instagramAccount = InstagramAccount::firstOrNew(['club_id' => $club->id]);
            
            $instagramAccount->fill([
                'instagram_username' => $validated['instagram_username'],
                'instagram_business_id' => $validated['instagram_business_id'],
                'access_token' => $validated['access_token'],
                'is_active' => true,
            ]);
            
            $instagramAccount->save();

            Log::info('Instagram credentials saved for club', [
                'club_id' => $club->id,
                'username' => $validated['instagram_username'],
            ]);

            $this->clubActivityService->recordClubActivity($club);

            return back()->with('success', 'Instagram account connected successfully!');
        } catch (\Exception $e) {
            Log::error('Error saving Instagram credentials', [
                'club_id' => $club->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to save Instagram credentials: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect Instagram account for a club
     */
    public function disconnect(Request $request)
    {
        $user = Auth::user();
        $club = Club::where('admin_id', $user->id)->first();

        if (!$club) {
            return back()->with('error', 'You do not have a club to manage.');
        }

        $this->clubActivityService->ensureClubIsActive($club);

        try {
            $instagramAccount = InstagramAccount::where('club_id', $club->id)->first();
            
            if ($instagramAccount) {
                $instagramAccount->update(['is_active' => false]);

                Log::info('Instagram account disconnected for club', ['club_id' => $club->id]);

                $this->clubActivityService->recordClubActivity($club);

                return back()->with('success', 'Instagram account disconnected.');
            }

            return back()->with('warning', 'No Instagram account found to disconnect.');
        } catch (\Exception $e) {
            Log::error('Error disconnecting Instagram account', [
                'club_id' => $club->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to disconnect Instagram account.');
        }
    }

    /**
     * Get Instagram account status for a club
     */
    public function getStatus()
    {
        $user = Auth::user();
        $club = Club::where('admin_id', $user->id)->first();

        if (!$club) {
            return response()->json(['error' => 'No club found'], 404);
        }

        $instagramAccount = $club->instagramAccount;

        return response()->json([
            'connected' => $instagramAccount && $instagramAccount->isTokenValid(),
            'username' => $instagramAccount?->instagram_username,
            'is_active' => $instagramAccount?->is_active,
            'last_post_at' => $instagramAccount?->last_post_at,
        ]);
    }
}
