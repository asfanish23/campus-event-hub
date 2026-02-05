<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Club;
use App\Models\StudentEventRegistration;
use App\Models\EventLike;
use App\Services\ContentBasedFilteringService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get all events with dates today or in the future (upcoming/ongoing)
        // Display only Upcoming or Ongoing status (exclude Completed)
        $allEvents = Event::where('date', '>=', now()->startOfDay())
            ->whereRaw("LOWER(status) IN (?, ?)", ['upcoming', 'ongoing'])
            ->orderBy('date', 'asc')
            ->get();
        
        // Use same query for upcomingEvents - no limit, show all upcoming/ongoing events
        $upcomingEvents = $allEvents;
        
        // Get registered events
        $registeredEventIds = StudentEventRegistration::where('user_id', $user->id)
            ->pluck('event_id')
            ->toArray();
        
        // Get liked events
        $likedEventIds = EventLike::where('user_id', $user->id)
            ->pluck('event_id')
            ->toArray();
        
        // Count registered events
        $registeredEventsCount = count($registeredEventIds);
        
        // Get recommended events using CBF algorithm (top 5 most recommended)
        $cbfService = new ContentBasedFilteringService();
        $recommendedEvents = $cbfService->getRecommendations($user, 5);
        
        // Get all clubs for sidebar
        $clubs = Club::all();
        
        return view('student.dashboard', [
            'user' => $user,
            'allEvents' => $allEvents,
            'upcomingEvents' => $upcomingEvents,
            'registeredEventIds' => $registeredEventIds,
            'likedEventIds' => $likedEventIds,
            'registeredEventsCount' => $registeredEventsCount,
            'recommendedEvents' => $recommendedEvents,
            'clubs' => $clubs,
        ]);
    }

    public function showClub(Club $club, Request $request)
    {
        // Get club's events
        $query = Event::where('club_id', $club->id)
            ->where('status', '!=', 'Completed')
            ->orderBy('date', 'asc');
        
        // Filter by year if provided
        if ($request->year) {
            $query->whereYear('date', $request->year);
        }
        
        $clubEvents = $query->get();
        
        // Get all available years from club events
        $years = Event::where('club_id', $club->id)
            ->where('status', '!=', 'Completed')
            ->selectRaw('YEAR(date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        // Get club's products (merchandise)
        $clubProducts = $club->products()->get();
        
        // Get all clubs for sidebar
        $clubs = Club::all();
        
        $selectedYear = $request->year;
        
        return view('student.club-profile', [
            'club' => $club,
            'clubEvents' => $clubEvents,
            'clubProducts' => $clubProducts,
            'clubs' => $clubs,
            'years' => $years,
            'selectedYear' => $selectedYear,
        ]);
    }

    public function showEvent(Event $event)
    {
        // Make sure event is not completed
        if ($event->status === 'Completed') {
            return redirect()->route('student.dashboard')->with('error', 'This event has been completed');
        }

        $user = Auth::user();
        $isRegistered = StudentEventRegistration::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->exists();

        $isLiked = EventLike::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->exists();

        $likeCount = $event->likes()->count();

        // Get liked events for the view
        $likedEventIds = EventLike::where('user_id', $user->id)
            ->pluck('event_id')
            ->toArray();

        // Get similar events using CBF algorithm
        $cbfService = new ContentBasedFilteringService();
        $similarEvents = $cbfService->getSimilarEvents($event, 5);

        return view('student.event-details', [
            'event' => $event,
            'isRegistered' => $isRegistered,
            'isLiked' => $isLiked,
            'likeCount' => $likeCount,
            'similarEvents' => $similarEvents,
            'likedEventIds' => $likedEventIds,
        ]);
    }

    public function registerEvent(Event $event)
    {
        $user = Auth::user();

        // Check if already registered
        $existing = StudentEventRegistration::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'You are already registered for this event'], 400);
        }

        // Create registration
        StudentEventRegistration::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Successfully registered for the event!']);
    }

    public function cancelRegistration(Event $event)
    {
        $user = Auth::user();

        // Find and delete registration
        $registration = StudentEventRegistration::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

        if (!$registration) {
            return response()->json(['success' => false, 'message' => 'You are not registered for this event'], 400);
        }

        $registration->delete();

        return response()->json(['success' => true, 'message' => 'Registration cancelled successfully!']);
    }

    public function calendar()
    {
        // Get all events (past, ongoing, and upcoming) sorted by date
        $events = Event::orderBy('date', 'asc')
            ->with('club')
            ->get();

        // Get user's liked events
        $user = Auth::user();
        $likedEventIds = EventLike::where('user_id', $user->id)
            ->pluck('event_id')
            ->toArray();

        // Get all clubs for sidebar
        $clubs = Club::all();

        return view('student.calendar', [
            'events' => $events,
            'likedEventIds' => $likedEventIds,
            'clubs' => $clubs,
        ]);
    }

    public function archive(Request $request)
    {
        $user = Auth::user();
        
        // Get all events with dates in the past (completed events based on date, not status field)
        $query = Event::where('date', '<', now()->startOfDay());
        
        // Filter by year if provided
        if ($request->has('year') && $request->year) {
            $query->whereYear('date', $request->year);
        }
        
        $completedEvents = $query->orderBy('date', 'desc')->get();
        
        // Get all years from completed events for the filter dropdown
        $years = Event::where('date', '<', now()->startOfDay())
            ->selectRaw('YEAR(date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        // Get registered events
        $registeredEventIds = StudentEventRegistration::where('user_id', $user->id)
            ->pluck('event_id')
            ->toArray();
        
        // Get liked events
        $likedEventIds = EventLike::where('user_id', $user->id)
            ->pluck('event_id')
            ->toArray();
        
        // Get all clubs for sidebar
        $clubs = Club::all();
        
        return view('student.archive', [
            'user' => $user,
            'completedEvents' => $completedEvents,
            'registeredEventIds' => $registeredEventIds,
            'likedEventIds' => $likedEventIds,
            'clubs' => $clubs,
            'years' => $years,
            'selectedYear' => $request->year,
        ]);
    }

    public function clubs()
    {
        $user = Auth::user();
        
        // Get all clubs
        $allClubs = Club::orderBy('name', 'asc')->get();
        
        // Get all clubs for sidebar
        $clubs = Club::all();
        
        return view('student.clubs', [
            'user' => $user,
            'allClubs' => $allClubs,
            'clubs' => $clubs,
        ]);
    }

    public function likeEvent(Event $event)
    {
        $user = Auth::user();

        // Check if already liked
        $existing = EventLike::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'You have already liked this event'], 400);
        }

        // Create like
        EventLike::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);

        $likeCount = $event->likes()->count();

        return response()->json(['success' => true, 'message' => 'Event liked!', 'likeCount' => $likeCount]);
    }

    public function unlikeEvent(Event $event)
    {
        $user = Auth::user();

        // Find and delete like
        $like = EventLike::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

        if (!$like) {
            return response()->json(['success' => false, 'message' => 'You have not liked this event'], 400);
        }

        $like->delete();

        $likeCount = $event->likes()->count();

        return response()->json(['success' => true, 'message' => 'Like removed!', 'likeCount' => $likeCount]);
    }
}
