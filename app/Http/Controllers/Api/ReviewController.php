<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function index(Event $event)
    {
        if ($event->getComputedStatus() !== 'completed') {
            return response()->json([
                'message' => 'Reviews are only available for completed events.',
                'data' => [],
                'average_rating' => 0,
                'reviews_count' => 0,
            ]);
        }

        $reviews = $event->reviews()
            ->with('user:id,name')
            ->latest('id')
            ->get();

        return response()->json([
            'data' => $reviews->map(fn (Review $review) => Review::toApiPayload($review))->values(),
            'average_rating' => round((float) $reviews->avg('rating'), 1),
            'reviews_count' => $reviews->count(),
        ]);
    }

    public function eligibility(Event $event)
    {
        $user = Auth::guard('sanctum')->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $eligibility = Review::buildEligibility($user, $event);

        return response()->json([
            'data' => [
                'has_joined' => $eligibility['has_joined'],
                'has_attendance' => $eligibility['has_attendance'],
                'event_completed' => $eligibility['event_completed'],
                'already_reviewed' => $eligibility['already_reviewed'],
                'can_submit_review' => $eligibility['can_submit_review'],
                'existing_review' => $eligibility['existing_review']
                    ? Review::toApiPayload($eligibility['existing_review'])
                    : null,
            ],
        ]);
    }

    public function store(Request $request, Event $event)
    {
        $user = Auth::guard('sanctum')->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($user->role !== 'student') {
            return response()->json(['message' => 'Only students can submit reviews.'], 403);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:3|max:2000',
        ]);

        $eligibility = Review::buildEligibility($user, $event);
        if (! $eligibility['can_submit_review']) {
            return response()->json([
                'message' => 'You are not eligible to submit a review for this event.',
                'data' => [
                    'has_joined' => $eligibility['has_joined'],
                    'has_attendance' => $eligibility['has_attendance'],
                    'event_completed' => $eligibility['event_completed'],
                    'already_reviewed' => $eligibility['already_reviewed'],
                ],
            ], 422);
        }

        $review = Review::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'reviewer_name' => $user->name,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'review_text' => $validated['comment'],
            'is_reported' => false,
        ]);

        $reviews = $event->reviews()->get();

        return response()->json([
            'message' => 'Review submitted successfully.',
            'review' => Review::toApiPayload($review->load('user:id,name')),
            'average_rating' => round((float) $reviews->avg('rating'), 1),
            'reviews_count' => $reviews->count(),
        ], 201);
    }
}
