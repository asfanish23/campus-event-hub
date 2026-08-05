<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\ClubFollower;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClubFollowController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function follow(Request $request, Club $club): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $existing = ClubFollower::where('user_id', $user->id)
            ->where('club_id', $club->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => 'Already following this club',
                'data' => [
                    'is_following' => true,
                    'followers_count' => $club->followers()->count(),
                ],
            ]);
        }

        ClubFollower::create([
            'user_id' => $user->id,
            'club_id' => $club->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Club followed successfully',
            'data' => [
                'is_following' => true,
                'followers_count' => $club->followers()->count(),
            ],
        ], 201);
    }

    public function unfollow(Request $request, Club $club): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $deleted = ClubFollower::where('user_id', $user->id)
            ->where('club_id', $club->id)
            ->delete();

        if (! $deleted) {
            return response()->json([
                'success' => true,
                'message' => 'You were not following this club',
                'data' => [
                    'is_following' => false,
                    'followers_count' => $club->followers()->count(),
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Unfollowed club successfully',
            'data' => [
                'is_following' => false,
                'followers_count' => $club->followers()->count(),
            ],
        ]);
    }

    public function followedClubs(): JsonResponse
    {
        $user = Auth::user();
        $followedClubIds = $user->followedClubs()->pluck('clubs.id')->all();

        $clubs = Club::query()
            ->with(['events', 'products.media', 'products.variants'])
            ->withCount('followers')
            ->whereIn('id', $followedClubIds)
            ->orderBy('name')
            ->get()
            ->map(fn (Club $club) => $this->formatClub($club, $followedClubIds));

        return response()->json([
            'success' => true,
            'count' => $clubs->count(),
            'data' => $clubs,
        ]);
    }

    public function followedEvents(Request $request): JsonResponse
    {
        $user = Auth::user();
        $followedClubIds = $user->followedClubs()->pluck('clubs.id')->all();

        $events = Event::query()
            ->with('club')
            ->whereIn('club_id', $followedClubIds)
            ->whereDate('date', '>=', now()->toDateString())
            ->latestEvents()
            ->limit((int) $request->query('limit', 20))
            ->get()
            ->map(fn (Event $event) => $this->formatEvent($event, $user));

        return response()->json([
            'success' => true,
            'count' => $events->count(),
            'data' => $events,
        ]);
    }

    private function formatClub(Club $club, array $followedClubIds): array
    {
        return array_merge($club->toArray(), [
            'followers_count' => $club->followers_count ?? $club->followers()->count(),
            'is_following' => in_array($club->id, $followedClubIds, true),
        ]);
    }

    private function formatEvent(Event $event, ?User $user = null): array
    {
        return array_merge($event->toArray(), [
            'event_date' => $event->date?->toDateString(),
            'status' => $event->getComputedStatus(),
            'likes' => $event->likes()->count(),
            'is_liked' => $user ? $user->likedEvents()->where('event_id', $event->id)->exists() : false,
        ]);
    }
}
