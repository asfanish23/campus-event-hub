<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Event;
use App\Services\ClubActivityService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(private ClubActivityService $clubActivityService)
    {
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|integer|exists:events,id',
            'user_id' => 'required|integer|exists:users,id',
            'attendee_name' => 'required|string|max:255',
            'matric_no' => 'nullable|string|max:255',
            'status' => 'required|in:Present,Absent',
        ]);

        $existing = Attendance::where('event_id', $validated['event_id'])
            ->where('user_id', $validated['user_id'])
            ->first();

        if ($existing) {
            $existing->update([
                'status' => $validated['status'],
                'attendee_name' => $validated['attendee_name'],
                'matric_no' => $validated['matric_no'] ?? null,
                'check_in_time' => $validated['status'] === 'Present'
                    ? now()->format('H:i:s')
                    : null,
            ]);

            $this->clubActivityService->recordClubActivity($existing->event->club);

            return redirect()->back()->with('success', 'Attendance updated successfully!');
        }

        Attendance::create([
            'event_id' => $validated['event_id'],
            'user_id' => $validated['user_id'],
            'attendee_name' => $validated['attendee_name'],
            'matric_no' => $validated['matric_no'] ?? null,
            'status' => $validated['status'],
            'check_in_time' => $validated['status'] === 'Present'
                ? now()->format('H:i:s')
                : null,
        ]);

        $this->clubActivityService->recordClubActivity(Event::find($validated['event_id'])?->club);

        return redirect()->back()->with('success', 'Attendance marked successfully!');
    }

    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'status' => 'required|in:Present,Absent',
        ]);

        $attendance->update($validated);

        $this->clubActivityService->recordClubActivity($attendance->event->club);

        return redirect()->back()->with('success', 'Attendance status updated successfully!');
    }
}
