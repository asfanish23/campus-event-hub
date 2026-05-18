<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClubNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClubNotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $notifications = ClubNotification::query()
            ->with(['club', 'event'])
            ->where('user_id', $user->id)
            ->when($request->boolean('unread_only'), function ($query) {
                $query->where('is_read', false);
            })
            ->orderByDesc('created_at')
            ->limit((int) $request->query('limit', 50))
            ->get()
            ->map(fn (ClubNotification $notification) => [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
                'is_read' => $notification->is_read,
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at,
                'club' => $notification->club ? [
                    'id' => $notification->club->id,
                    'name' => $notification->club->name,
                    'category' => $notification->club->category,
                ] : null,
                'event' => $notification->event ? [
                    'id' => $notification->event->id,
                    'name' => $notification->event->name,
                    'date' => $notification->event->date,
                ] : null,
            ]);

        return response()->json([
            'success' => true,
            'count' => $notifications->count(),
            'data' => $notifications,
        ]);
    }

    public function markAsRead(ClubNotification $notification): JsonResponse
    {
        $user = Auth::user();

        if ($notification->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Not authorized to update this notification',
            ], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }
}
