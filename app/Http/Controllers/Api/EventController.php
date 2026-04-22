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
            $eventData['is_joined'] = $user ? $user->registrations()->where('event_id', $event->id)->exists() : false;
            $eventData['likes'] = $event->likes()->count();
            $eventData['joined_count'] = $event->registrations()->count();

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

    /**
     * Join an event
     */
    public function join(Request $request, Event $event)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Check if user is already registered
            $existing = $user->registrations()
                ->where('event_id', $event->id)
                ->first();

            if ($existing) {
                return response()->json([
                    'message' => 'You have already joined this event',
                    'data' => [
                        'joined' => true,
                        'registered_at' => $existing->registered_at
                    ]
                ], 200);
            }

            // Create registration
            $registration = $user->registrations()->create([
                'event_id' => $event->id,
                'registered_at' => now()
            ]);

            return response()->json([
                'message' => 'Successfully joined the event',
                'data' => [
                    'joined' => true,
                    'registered_at' => $registration->registered_at,
                    'event' => $event
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to join event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Leave an event
     */
    public function leave(Request $request, Event $event)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Find and delete registration
            $deleted = $user->registrations()
                ->where('event_id', $event->id)
                ->delete();

            if (!$deleted) {
                return response()->json([
                    'message' => 'You have not joined this event',
                    'data' => ['joined' => false]
                ], 200);
            }

            return response()->json([
                'message' => 'Successfully left the event',
                'data' => ['joined' => false]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to leave event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get join status for an event
     */
    public function getJoinStatus(Event $event)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            
            $joined = $user ? $user->registrations()->where('event_id', $event->id)->exists() : false;
            $joinedCount = $event->registrations()->count();

            return response()->json([
                'data' => [
                    'event_id' => $event->id,
                    'is_joined' => $joined,
                    'joined_count' => $joinedCount
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get join status: ' . $e->getMessage()
            ], 500);
        }
    }
}
