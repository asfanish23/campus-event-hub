<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventLike;
use App\Models\StudentEventRegistration;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    private function baseEventQuery()
    {
        return Event::with('club')
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc');
    }

    private function safeLikesCount(Event $event): int
    {
        try {
            return $event->likes()->count();
        } catch (\Throwable $e) {
            Log::warning('Unable to count event likes', [
                'event_id' => $event->id,
                'event_name' => $event->name ?? null,
                'error_message' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    private function safeIsLiked(Event $event, $user): bool
    {
        if (!$user) {
            return false;
        }

        try {
            return $user->likedEvents()->where('event_id', $event->id)->exists();
        } catch (\Throwable $e) {
            Log::warning('Unable to resolve event like state', [
                'event_id' => $event->id,
                'user_id' => $user->id,
                'error_message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function feedQuery()
    {
        return $this->baseEventQuery()
            ->whereDate('date', '>=', now()->toDateString());
    }

    private function formatEventData(Event $event, $user): array
    {
        $eventData = $event->toArray();
        $eventData['event_date'] = $event->date?->toDateString();
        // Use computed status instead of database status for accurate filtering
        $eventData['status'] = $event->getComputedStatus();
        $eventData['is_liked'] = $this->safeIsLiked($event, $user);
        $eventData['is_joined'] = $user
            ? StudentEventRegistration::where('user_id', $user->id)->where('event_id', $event->id)->exists()
            : false;
        $eventData['likes'] = $this->safeLikesCount($event);
        $eventData['joined_count'] = $event->registrations()->count();
        $eventData['average_rating'] = round((float) $event->reviews()->avg('rating'), 1);
        $eventData['reviews_count'] = $event->reviews()->count();

        return $eventData;
    }

    /**
     * Get all events
     */
    public function index(Request $request)
    {
        try {
            $query = $this->baseEventQuery();

            if ($request->filled('selected_date')) {
                $query->whereDate('date', $request->input('selected_date'));
            } elseif (!$request->boolean('include_all')) {
                $query->whereDate('date', '>=', now()->toDateString());
            }

            $events = $query->get();

            // Filter by computed status if provided
            if ($request->filled('status')) {
                $requestedStatus = strtolower($request->input('status'));
                $events = $events->filter(function ($event) use ($requestedStatus) {
                    return $event->getComputedStatus() === $requestedStatus;
                });
            }

            $user = Auth::guard('sanctum')->user();
            
            // Format events with like status and computed status
            $formattedEvents = $events->map(function ($event) use ($user) {
                try {
                    return $this->formatEventData($event, $user);
                } catch (\Throwable $e) {
                    Log::warning('Skipping malformed event row in API response', [
                        'event_id' => $event->id ?? null,
                        'event_name' => $event->name ?? null,
                        'error_message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ]);

                    return null;
                }
            })->filter()->values();

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
            $eventData['event_date'] = $event->date?->toDateString();
            $eventData['status'] = $event->getComputedStatus();
            $eventData['is_liked'] = $this->safeIsLiked($event, $user);
            $eventData['is_joined'] = $user
                ? StudentEventRegistration::where('user_id', $user->id)->where('event_id', $event->id)->exists()
                : false;
            $eventData['likes'] = $this->safeLikesCount($event);
            $eventData['joined_count'] = $event->registrations()->count();
            $eventData['average_rating'] = round((float) $event->reviews()->avg('rating'), 1);
            $eventData['reviews_count'] = $event->reviews()->count();

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

            $events = $this->feedQuery()
                ->where(function ($builder) use ($query) {
                    $builder->where('name', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
                })
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
    public function join(Request $request, $eventId)
    {
        try {
            Log::info('Join event request', [
                'raw_event_id' => $eventId,
                'event_id_type' => gettype($eventId),
                'route_params' => $request->route()->parameters(),
                'timestamp' => now()
            ]);
            
            // Sanitize and validate event ID
            $eventId = (int) $eventId;
            if ($eventId <= 0) {
                Log::warning('Invalid event ID', [
                    'event_id' => $eventId,
                    'original' => $request->route('eventId')
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid event ID provided',
                    'error_code' => 'INVALID_EVENT_ID'
                ], 400);
            }
            
            $user = Auth::guard('sanctum')->user();
            
            if (!$user) {
                Log::warning('Join event failed: User not authenticated', [
                    'event_id' => $eventId
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - Please login first',
                    'error_code' => 'UNAUTHENTICATED'
                ], 401);
            }

            // Manually query for event with detailed logging
            Log::debug('Querying event table', [
                'event_id' => $eventId,
                'user_id' => $user->id
            ]);
            
            $event = Event::find($eventId);
            
            if (!$event) {
                // Log detailed diagnostic info
                $totalEvents = Event::count();
                $eventIds = Event::pluck('id')->toArray();
                
                Log::warning('Event not found in database', [
                    'searched_id' => $eventId,
                    'user_id' => $user->id,
                    'total_events_in_db' => $totalEvents,
                    'available_event_ids' => $eventIds,
                    'table_name' => (new Event())->getTable()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => "Event with ID $eventId not found in database",
                    'error_code' => 'EVENT_NOT_FOUND',
                    'debug' => [
                        'searched_id' => $eventId,
                        'total_events' => $totalEvents,
                        'available_ids' => count($eventIds) > 10 ? array_slice($eventIds, 0, 10) : $eventIds
                    ]
                ], 404);
            }

            Log::info('Event found', [
                'event_id' => $event->id,
                'event_name' => $event->name,
                'user_id' => $user->id
            ]);

            // Check if user is already registered
            $existing = StudentEventRegistration::where('user_id', $user->id)
                ->where('event_id', $event->id)
                ->first();

            if ($existing) {
                Log::info('User already joined event', [
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

            // Prevent new joins for events that have already completed.
            if ($event->getComputedStatus() === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'This event has ended. Registration is closed.',
                    'error_code' => 'EVENT_COMPLETED'
                ], 422);
            }

            // Create registration
            Log::info('Creating event registration', [
                'event_id' => $event->id,
                'user_id' => $user->id
            ]);
            
            $registration = StudentEventRegistration::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'registered_at' => now()
            ]);

            Log::info('Successfully joined event', [
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
            Log::error('Error joining event', [
                'event_id' => $eventId ?? 'null',
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
    public function leave(Request $request, $eventId)
    {
        try {
            Log::info('Leave event request', [
                'raw_event_id' => $eventId,
                'timestamp' => now()
            ]);
            
            // Sanitize and validate event ID
            $eventId = (int) $eventId;
            if ($eventId <= 0) {
                Log::warning('Invalid event ID for leave', [
                    'event_id' => $eventId
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid event ID provided',
                    'error_code' => 'INVALID_EVENT_ID'
                ], 400);
            }
            
            $user = Auth::guard('sanctum')->user();
            
            if (!$user) {
                Log::warning('Leave event failed: User not authenticated', [
                    'event_id' => $eventId
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - Please login first',
                    'error_code' => 'UNAUTHENTICATED'
                ], 401);
            }

            // Verify event exists
            $event = Event::find($eventId);
            if (!$event) {
                Log::warning('Event not found for leave', [
                    'searched_id' => $eventId,
                    'user_id' => $user->id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => "Event with ID $eventId not found",
                    'error_code' => 'EVENT_NOT_FOUND'
                ], 404);
            }

            // Find and delete registration
            $deleted = StudentEventRegistration::where('user_id', $user->id)
                ->where('event_id', $event->id)
                ->delete();

            if (!$deleted) {
                Log::info('User had not joined event', [
                    'event_id' => $event->id,
                    'user_id' => $user->id
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'You have not joined this event',
                    'data' => ['joined' => false, 'event_id' => $event->id]
                ], 200);
            }

            Log::info('Successfully left event', [
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
            Log::error('Error leaving event', [
                'event_id' => $eventId ?? 'null',
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
    public function getJoinStatus($eventId)
    {
        try {
            // Sanitize and validate event ID
            $eventId = (int) $eventId;
            if ($eventId <= 0) {
                return response()->json([
                    'data' => [
                        'event_id' => $eventId,
                        'is_joined' => false,
                        'joined_count' => 0
                    ]
                ], 200);
            }
            
            $event = Event::find($eventId);
            if (!$event) {
                return response()->json([
                    'data' => [
                        'event_id' => $eventId,
                        'is_joined' => false,
                        'joined_count' => 0
                    ]
                ], 200);
            }
            
            $user = Auth::guard('sanctum')->user();
            
            $joined = $user
                ? StudentEventRegistration::where('user_id', $user->id)->where('event_id', $event->id)->exists()
                : false;
            $joinedCount = $event->registrations()->count();

            return response()->json([
                'data' => [
                    'event_id' => $event->id,
                    'is_joined' => $joined,
                    'joined_count' => $joinedCount
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error getting join status', [
                'event_id' => $eventId ?? 'null',
                'error_message' => $e->getMessage()
            ]);
            
            return response()->json([
                'data' => [
                    'event_id' => $eventId ?? 0,
                    'is_joined' => false,
                    'joined_count' => 0
                ]
            ], 200);
        }
    }

    /**
     * Like an event
     */
    public function like(Request $request, $eventId)
    {
        try {
            $user = Auth::guard('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - Please login first',
                    'error_code' => 'UNAUTHENTICATED'
                ], 401);
            }

            $eventId = (int) $eventId;
            if ($eventId <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid event ID provided',
                    'error_code' => 'INVALID_EVENT_ID'
                ], 400);
            }

            $event = Event::find($eventId);
            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event not found',
                    'error_code' => 'EVENT_NOT_FOUND'
                ], 404);
            }

            $existingLike = EventLike::where('user_id', $user->id)
                ->where('event_id', $event->id)
                ->first();

            if ($existingLike) {
                return response()->json([
                    'success' => true,
                    'message' => 'Event already liked',
                    'data' => [
                        'event_id' => $event->id,
                        'is_liked' => true,
                        'likes_count' => $event->likes()->count()
                    ]
                ], 200);
            }

            EventLike::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Event liked successfully',
                'data' => [
                    'event_id' => $event->id,
                    'is_liked' => true,
                    'likes_count' => $event->likes()->count()
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error liking event', [
                'event_id' => $eventId ?? 'null',
                'user_id' => Auth::guard('sanctum')->user()?->id ?? 'null',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to like event',
                'error_code' => 'LIKE_FAILED'
            ], 500);
        }
    }

    /**
     * Unlike an event
     */
    public function unlike(Request $request, $eventId)
    {
        try {
            $user = Auth::guard('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - Please login first',
                    'error_code' => 'UNAUTHENTICATED'
                ], 401);
            }

            $eventId = (int) $eventId;
            if ($eventId <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid event ID provided',
                    'error_code' => 'INVALID_EVENT_ID'
                ], 400);
            }

            $event = Event::find($eventId);
            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event not found',
                    'error_code' => 'EVENT_NOT_FOUND'
                ], 404);
            }

            EventLike::where('user_id', $user->id)
                ->where('event_id', $event->id)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Event unliked successfully',
                'data' => [
                    'event_id' => $event->id,
                    'is_liked' => false,
                    'likes_count' => $event->likes()->count()
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error unliking event', [
                'event_id' => $eventId ?? 'null',
                'user_id' => Auth::guard('sanctum')->user()?->id ?? 'null',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to unlike event',
                'error_code' => 'UNLIKE_FAILED'
            ], 500);
        }
    }

    /**
     * Get like status for an event
     */
    public function getLikeStatus($eventId)
    {
        try {
            $eventId = (int) $eventId;
            if ($eventId <= 0) {
                return response()->json([
                    'data' => [
                        'event_id' => $eventId,
                        'is_liked' => false,
                        'likes_count' => 0
                    ]
                ], 200);
            }

            $event = Event::find($eventId);
            if (!$event) {
                return response()->json([
                    'data' => [
                        'event_id' => $eventId,
                        'is_liked' => false,
                        'likes_count' => 0
                    ]
                ], 200);
            }

            $user = Auth::guard('sanctum')->user();
            $isLiked = $user ? EventLike::where('user_id', $user->id)
                ->where('event_id', $event->id)
                ->exists() : false;

            return response()->json([
                'data' => [
                    'event_id' => $event->id,
                    'is_liked' => $isLiked,
                    'likes_count' => $event->likes()->count()
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'data' => [
                    'event_id' => $eventId ?? 0,
                    'is_liked' => false,
                    'likes_count' => 0
                ]
            ], 200);
        }
    }

    /**
     * Get liked events for a user
     */
    public function getUserLikedEvents(Request $request, $userId)
    {
        try {
            $authUser = Auth::guard('sanctum')->user();

            if (!$authUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - Please login first',
                    'error_code' => 'UNAUTHENTICATED'
                ], 401);
            }

            $userId = (int) $userId;
            if ($userId <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid user ID provided',
                    'error_code' => 'INVALID_USER_ID'
                ], 400);
            }

            if ($authUser->id !== $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden',
                    'error_code' => 'FORBIDDEN'
                ], 403);
            }

            $user = User::find($userId);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'error_code' => 'USER_NOT_FOUND'
                ], 404);
            }

            $likedEvents = $user->likedEvents()->with('club')->orderBy('date', 'desc')->get();

            $data = $likedEvents->map(function ($event) {
                $eventData = $event->toArray();
                $eventData['is_liked'] = true;
                $eventData['likes'] = $this->safeLikesCount($event);
                return $eventData;
            });

            return response()->json([
                'success' => true,
                'message' => 'Liked events retrieved successfully',
                'count' => $data->count(),
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching user liked events', [
                'user_id' => $userId ?? 'null',
                'auth_user_id' => Auth::guard('sanctum')->user()?->id ?? 'null',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch liked events',
                'error_code' => 'FETCH_LIKED_EVENTS_FAILED'
            ], 500);
        }
    }
}
