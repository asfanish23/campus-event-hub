<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Club;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ClubController extends Controller
{
    /**
     * Get all clubs
     */
    public function index()
    {
        try {
            $user = Auth::guard('sanctum')->user();
            $followedClubIds = $user ? $user->followedClubs()->pluck('clubs.id')->all() : [];

            $clubs = Club::with(['events', 'products.media', 'products.variants'])
                ->withCount('followers')
                ->orderBy('name')
                ->get()
                ->map(function (Club $club) use ($followedClubIds) {
                    return $this->formatClub($club, $followedClubIds);
                });
            return response()->json([
                'data' => $clubs,
                'count' => $clubs->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch clubs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single club by ID
     */
    public function show(Club $club)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            $followedClubIds = $user ? $user->followedClubs()->pluck('clubs.id')->all() : [];

            // Eagerly load events and products with media
            $club->load('events', 'products.media', 'products.variants');
            $club->loadCount('followers');
            return response()->json([
                'data' => $this->formatClub($club, $followedClubIds)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Club not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Search clubs
     */
    public function search(Request $request)
    {
        try {
            $query = $request->query('q', '');
            $category = $request->query('category');
            $user = Auth::guard('sanctum')->user();
            $followedClubIds = $user ? $user->followedClubs()->pluck('clubs.id')->all() : [];
            
            $clubs = Club::query();
            
            if (!empty($query)) {
                $clubs->where(function ($builder) use ($query) {
                    $builder->where('name', 'LIKE', "%$query%")
                        ->orWhere('description', 'LIKE', "%$query%");
                });
            }
            
            if (!empty($category) && $category !== 'All') {
                $clubs->where('category', $category);
            }
            
            $clubs = $clubs->with(['events', 'products.media', 'products.variants'])
                ->withCount('followers')
                ->orderBy('name')
                ->get()
                ->map(function (Club $club) use ($followedClubIds) {
                    return $this->formatClub($club, $followedClubIds);
                });
            
            return response()->json([
                'data' => $clubs,
                'count' => $clubs->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get events for a specific club
     */
    public function getEvents(Club $club)
    {
        try {
            $events = $club->events()->latestEvents()->get();
            return response()->json([
                'data' => $events,
                'count' => $events->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch club events',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function formatClub(Club $club, array $followedClubIds = []): array
    {
        return array_merge($club->toArray(), [
            'followers_count' => $club->followers_count ?? $club->followers()->count(),
            'is_following' => in_array($club->id, $followedClubIds, true),
        ]);
    }
}
