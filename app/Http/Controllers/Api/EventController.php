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
            
            // Log request details
            \Log::info('Join event request', [
                'event_id' => $event->id,
                'user_id' => $user?->id,
                'user_authenticated' => $user !== null,
                'timestamp' => now()
            ]);
            
            if (!$user) {
                \Log::warning('Join event failed: User not authenticated', [
                    'event_id' => $event->id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - Please login first',
                    'error_code' => 'UNAUTHENTICATED'
                ], 401);
            }

            // Validate event exists
            if (!$event || !$event->id) {
                \Log::warning('Join event failed: Invalid event', [
                    'event_id' => $event->id ?? 'null',
                    'user_id' => $user->id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Event not found',
                    'error_code' => 'EVENT_NOT_FOUND'
                ], 404);
            }

            // Check if user is already registered
            $existing = $user->registrations()
                ->where('event_id', $event->id)
                ->first();

            if ($existing) {
                \Log::info('User already joined event', [
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'registered_at' => $existing->registered_at
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'You have already joined this event',
                    'data' => [
                        'joined' => true,
                        'registered_at' => $existing->registered_at,
                        'event_id' => $event->id
                    ]
                ], 200);
            }

            // Create registration
            \Log::info('Creating event registration', [
                'event_id' => $event->id,
                'user_id' => $user->id
            ]);
            
            $registration = $user->registrations()->create([
                'event_id' => $event->id,
                'registered_at' => now()
            ]);

            \Log::info('Successfully joined event', [
                'event_id' => $event->id,
                'user_id' => $user->id,
                'registration_id' => $registration->id,
                'registered_at' => $registration->registered_at
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully joined the event',
                'data' => [
                    'joined' => true,
                    'registered_at' => $registration->registered_at,
                    'event_id' => $event->id,
                    'user_id' => $user->id
                ]
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error joining event', [
                'event_id' => $event->id ?? 'null',
                'user_id' => Auth::guard('sanctum')->user()?->id ?? 'null',
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to join event: ' . $e->getMessage(),
                'error_code' => 'JOIN_FAILED',
                'error_details' => config('app.debug') ? $e->getMessage() : null
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
            
            \Log::info('Leave event request', [
                'event_id' => $event->id,
                'user_id' => $user?->id,
                'user_authenticated' => $user !== null
            ]);
            
            if (!$user) {
                \Log::warning('Leave event failed: User not authenticated', [
                    'event_id' => $event->id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - Please login first',
                    'error_code' => 'UNAUTHENTICATED'
                ], 401);
            }

            // Find and delete registration
            $deleted = $user->registrations()
                ->where('event_id', $event->id)
                ->delete();

            if (!$deleted) {
                \Log::info('User had not joined event', [
                    'event_id' => $event->id,
                    'user_id' => $user->id
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'You have not joined this event',
                    'data' => ['joined' => false, 'event_id' => $event->id]
                ], 200);
            }

            \Log::info('Successfully left event', [
                'event_id' => $event->id,
                'user_id' => $user->id,
                'deleted_count' => $deleted
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully left the event',
                'data' => ['joined' => false, 'event_id' => $event->id]
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error leaving event', [
                'event_id' => $event->id ?? 'null',
                'user_id' => Auth::guard('sanctum')->user()?->id ?? 'null',
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to leave event: ' . $e->getMessage(),
                'error_code' => 'LEAVE_FAILED',
                'error_details' => config('app.debug') ? $e->getMessage() : null
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
