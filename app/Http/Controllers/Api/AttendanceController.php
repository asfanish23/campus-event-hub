<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\StudentEventRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * Mark attendance from QR scan.
     */
    public function scan(Request $request)
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

            $validated = $request->validate([
                'event_id' => 'required|integer|min:1',
                'user_id' => 'sometimes|integer|min:1',
            ]);

            $eventId = (int) $validated['event_id'];
            $requestedUserId = isset($validated['user_id']) ? (int) $validated['user_id'] : null;

            \Log::info('Attendance scan request received', [
                'auth_user_id' => $user->id,
                'requested_user_id' => $requestedUserId,
                'event_id' => $eventId,
            ]);

            // Prevent spoofing if client sends user_id.
            if ($requestedUserId !== null && $requestedUserId !== (int) $user->id) {
                \Log::warning('Attendance scan blocked: user_id mismatch', [
                    'auth_user_id' => $user->id,
                    'requested_user_id' => $requestedUserId,
                    'event_id' => $eventId,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid user context',
                    'error_code' => 'USER_ID_MISMATCH'
                ], 403);
            }

            $event = Event::find($eventId);
            if (!$event) {
                \Log::warning('Attendance scan failed: event not found', [
                    'user_id' => $user->id,
                    'event_id' => $eventId,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Event not found',
                    'error_code' => 'EVENT_NOT_FOUND'
                ], 404);
            }

            $hasJoined = StudentEventRegistration::where('user_id', $user->id)
                ->where('event_id', $eventId)
                ->exists();

            if (!$hasJoined) {
                \Log::warning('Attendance scan failed: user not joined event', [
                    'user_id' => $user->id,
                    'event_id' => $eventId,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'You must join this event before checking in',
                    'error_code' => 'NOT_JOINED'
                ], 422);
            }

            $existing = Attendance::where('user_id', $user->id)
                ->where('event_id', $eventId)
                ->first();

            if ($existing) {
                \Log::info('Attendance scan duplicate prevented', [
                    'attendance_id' => $existing->id,
                    'user_id' => $user->id,
                    'event_id' => $eventId,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Attendance already marked',
                    'data' => [
                        'attendance_id' => $existing->id,
                        'user_id' => $existing->user_id,
                        'event_id' => $existing->event_id,
                        'check_in_time' => $existing->check_in_time,
                        'status' => $existing->status,
                    ]
                ], 200);
            }

            $attendance = Attendance::create([
                'user_id' => $user->id,
                'event_id' => $eventId,
                'attendee_name' => $user->name ?? 'Unknown',
                'matric_no' => $user->student_id ?? ('UID-' . $user->id),
                'check_in_time' => now()->format('H:i:s'),
                'status' => 'Present',
            ]);

            \Log::info('Attendance inserted successfully', [
                'attendance_id' => $attendance->id,
                'user_id' => $attendance->user_id,
                'event_id' => $attendance->event_id,
                'check_in_time' => $attendance->check_in_time,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Attendance marked successfully',
                'data' => [
                    'attendance_id' => $attendance->id,
                    'user_id' => $attendance->user_id,
                    'event_id' => $attendance->event_id,
                    'check_in_time' => $attendance->check_in_time,
                    'status' => $attendance->status,
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'error_code' => 'VALIDATION_FAILED'
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Attendance scan failed', [
                'user_id' => Auth::guard('sanctum')->user()?->id ?? 'null',
                'event_id' => $request->input('event_id'),
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark attendance',
                'error_code' => 'ATTENDANCE_SCAN_FAILED'
            ], 500);
        }
    }
}
