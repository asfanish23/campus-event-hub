<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class AdminUserController extends Controller
{
    /**
     * Return profile + activity data for the given user id.
     * Only accessible by super admin (enforced via middleware on route).
     */
    public function show(Request $request, $id)
    {
        $user = User::with(['club', 'registrations.event', 'likedEvents'])->find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Basic info (exclude sensitive fields)
        $basic = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'faculty_or_program' => $user->faculty ?? $user->program ?? null,
            'account_status' => $user->admin_status ?? null,
            'joined_date' => optional($user->created_at)->toDateString(),
        ];

        // Activity
        $totalJoined = $user->registrations()->count();
        $totalLiked = $user->likedEvents()->count();

        $activity = [
            'total_events_joined' => $totalJoined,
            'total_events_liked' => $totalLiked,
            // approximate last active (use updated_at as fallback)
            'last_active_date' => optional($user->updated_at)->toDateString(),
        ];

        // Event history
        $joined = $user->registrations->map(function ($reg) {
            return [
                'event_name' => optional($reg->event)->name,
                'event_date' => optional($reg->event)->date ? optional($reg->event->date)->toDateString() : null,
                'registered_at' => optional($reg->registered_at)->toDateTimeString(),
            ];
        })->filter()->values();

        $liked = $user->likedEvents->map(function ($ev) {
            return [
                'event_name' => $ev->name ?? null,
                'event_date' => optional($ev->date)->toDateString() ?? null,
            ];
        })->filter()->values();

        return response()->json([
            'basic' => $basic,
            'activity' => $activity,
            'joined_events' => $joined,
            'liked_events' => $liked,
        ]);
    }
}
