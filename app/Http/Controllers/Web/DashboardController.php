<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Event;
use App\Models\Attendance;
use App\Services\InstagramNotificationService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private InstagramNotificationService $notificationService;

    public function __construct(InstagramNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $user = Auth::user();
        $club = Club::find($user->club_id);
        
        // Check if user is a club user or student user
        if (!$club) {
            // Student dashboard - redirect to student dashboard
            return redirect()->route('student.dashboard');
        }
        
        // Get club statistics
        $totalEvents = Event::where('club_id', $club->id)->count();
        $upcomingEvents = Event::where('club_id', $club->id)
            ->whereComputedStatus('upcoming')
            ->count();
        
        // Calculate average attendance
        $attendances = Attendance::whereIn('event_id', Event::where('club_id', $club->id)->pluck('id'))
            ->get();
        $avgAttendance = $attendances->count() > 0 
            ? round(($attendances->where('status', 'Present')->count() / $attendances->count()) * 100) 
            : 0;
        
        // Get merchandise sales
        $merchSales = 0; // This would need order data
        
        // Get Instagram notifications
        $instagramNotifications = $this->notificationService->getRecentNotifications($club->id, 5);
        
        return view('dashboard.index', [
            'user' => $user,
            'club' => $club,
            'totalEvents' => $totalEvents,
            'upcomingEvents' => $upcomingEvents,
            'avgAttendance' => $avgAttendance,
            'merchSales' => $merchSales,
            'instagramNotifications' => $instagramNotifications,
        ]);
    }
}
