<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller
{
    /**
     * Get all events
     */
    public function index()
    {
        try {
            $events = Event::where('status', 'active')
                ->orWhere('status', 'upcoming')
                ->orderBy('event_date', 'asc')
                ->get();

            return response()->json([
                'data' => $events,
                'count' => $events->count()
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
            $event = Event::findOrFail($id);

            return response()->json([
                'data' => $event
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

            $events = Event::where('title', 'like', "%$query%")
                ->orWhere('description', 'like', "%$query%")
                ->where(function ($q) {
                    $q->where('status', 'active')
                        ->orWhere('status', 'upcoming');
                })
                ->orderBy('event_date', 'asc')
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
