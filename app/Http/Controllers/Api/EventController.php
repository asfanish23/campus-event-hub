<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    /**
     * Get all events
     */
    public function index(Request $request)
    {
        try {
            $events = Event::with('club')
                ->whereIn('status', ['Upcoming', 'Currently Running'])
                ->orderBy('date', 'asc')
                ->get();

            $user = Auth::guard('sanctum')->user();
            
            // Format events with like status if user is authenticated
            $formattedEvents = $events->map(function ($event) use ($user) {
                $eventData = $event->toArray();
                $eventData['is_liked'] = $user ? $user->likedEvents()->where('event_id', $event->id)->exists() : false;
                $eventData['likes'] = $event->likes()->count();
                return $eventData;
            });

            return response()->json([
                'data' => $formattedEvents,
                'count' => $formattedEvents->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to load events: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single event by ID
     */
    public function show($id)
    {
        try {
            $event = Event::with('club')->findOrFail($id);
            $user = Auth::guard('sanctum')->user();

            $eventData = $event->toArray();
            $eventData['is_liked'] = $user ? $user->likedEvents()->where('event_id', $event->id)->exists() : false;
            $eventData['likes'] = $event->likes()->count();

            return response()->json([
                'data' => $eventData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Event not found'
            ], 404);
        }
    }

    /**
     * Search events
     */
    public function search(Request $request)
    {
        try {
            $query = $request->input('q', '');

            $events = Event::with('club')
                ->where('name', 'like', "%$query%")
                ->orWhere('description', 'like', "%$query%")
                ->whereIn('status', ['Upcoming', 'Currently Running'])
                ->orderBy('date', 'asc')
                ->get();

            return response()->json([
                'data' => $events,
                'count' => $events->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
