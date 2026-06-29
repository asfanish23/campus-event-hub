<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventLike;
use App\Services\ContentBasedFilteringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecommendationController extends Controller
{
    protected ContentBasedFilteringService $cbfService;

    public function __construct(ContentBasedFilteringService $cbfService)
    {
        $this->cbfService = $cbfService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Get personalized event recommendations for the authenticated user
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getRecommendations(Request $request): JsonResponse
    {
        $limit = $request->query('limit', 10);
        $user = Auth::guard('sanctum')->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'count' => 0,
                'data' => [],
            ], 401);
        }

        $recommendations = $this->cbfService->getRecommendations($user, $limit);
        $likedEventIds = EventLike::where('user_id', $user->id)->pluck('event_id')->all();

        return response()->json([
            'success' => true,
            'message' => 'Recommendations generated successfully',
            'count' => $recommendations->count(),
            'data' => $recommendations->map(function (Event $event) use ($likedEventIds) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'description' => $event->description,
                    'category' => $event->category,
                    'date' => $event->date,
                    'location' => $event->location,
                    'club' => $event->club?->name,
                    'club_id' => $event->club_id,
                    'event_image' => $event->event_image,
                    'likes_count' => $event->likes()->count(),
                    'is_liked' => in_array($event->id, $likedEventIds, true),
                ];
            }),
        ]);
    }

    /**
     * Get events similar to a specific event
     * 
     * @param Event $event
     * @param Request $request
     * @return JsonResponse
     */
    public function getSimilarEvents(Event $event, Request $request): JsonResponse
    {
        $limit = $request->query('limit', 5);
        $user = Auth::guard('sanctum')->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'count' => 0,
                'data' => [],
            ], 401);
        }

        $likedEventIds = EventLike::where('user_id', $user->id)->pluck('event_id')->all();

        $similarEvents = $this->cbfService->getSimilarEvents($event, $limit);

        return response()->json([
            'success' => true,
            'message' => 'Similar events retrieved successfully',
            'count' => $similarEvents->count(),
            'data' => $similarEvents->map(function (Event $similarEvent) use ($likedEventIds) {
                return [
                    'id' => $similarEvent->id,
                    'name' => $similarEvent->name,
                    'description' => $similarEvent->description,
                    'category' => $similarEvent->category,
                    'date' => $similarEvent->date,
                    'location' => $similarEvent->location,
                    'club' => $similarEvent->club?->name,
                    'club_id' => $similarEvent->club_id,
                    'event_image' => $similarEvent->event_image,
                    'likes_count' => $similarEvent->likes()->count(),
                    'is_liked' => in_array($similarEvent->id, $likedEventIds, true),
                ];
            }),
        ]);
    }

    /**
     * Like an event (increase recommendation accuracy)
     * 
     * @param Event $event
     * @return JsonResponse
     */
    public function likeEvent(Event $event): JsonResponse
    {
        $user = Auth::user();

        // Check if already liked
        $existingLike = EventLike::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

        if ($existingLike) {
            return response()->json([
                'success' => false,
                'message' => 'Event already liked',
            ], 409);
        }

        // Create like
        EventLike::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Event liked successfully',
            'data' => [
                'event_id' => $event->id,
                'likes_count' => $event->likes()->count() + 1,
            ],
        ], 201);
    }

    /**
     * Unlike an event
     * 
     * @param Event $event
     * @return JsonResponse
     */
    public function unlikeEvent(Event $event): JsonResponse
    {
        $user = Auth::user();

        $deleted = EventLike::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Like not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Event unliked successfully',
            'data' => [
                'event_id' => $event->id,
                'likes_count' => $event->likes()->count(),
            ],
        ]);
    }

    /**
     * Get user's liked events
     * 
     * @return JsonResponse
     */
    public function getUserLikes(): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'count' => 0,
                'data' => [],
            ], 401);
        }

        $likedEventIds = EventLike::where('user_id', $user->id)->pluck('event_id');
        $likedEvents = Event::with('club')->whereIn('id', $likedEventIds)->get();

        return response()->json([
            'success' => true,
            'message' => 'User likes retrieved successfully',
            'count' => $likedEvents->count(),
            'data' => $likedEvents->map(function (Event $event) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'category' => $event->category,
                    'date' => $event->date,
                    'club' => $event->club?->name,
                ];
            }),
        ]);
    }

    /**
     * Get event like count and user's like status
     * 
     * @param Event $event
     * @return JsonResponse
     */
    public function getEventLikeStatus(Event $event): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        $isLiked = $user
            ? EventLike::where('user_id', $user->id)->where('event_id', $event->id)->exists()
            : false;

        return response()->json([
            'success' => true,
            'data' => [
                'event_id' => $event->id,
                'likes_count' => $event->likes()->count(),
                'is_liked_by_user' => $isLiked,
            ],
        ]);
    }
}
