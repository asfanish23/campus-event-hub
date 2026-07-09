<?php

namespace App\Services;

use App\Models\ClubFollower;
use App\Models\ClubNotification;
use App\Models\Event;
use Illuminate\Support\Facades\Log;

class ClubNotificationService
{
    public function notifyClubFollowers(Event $event): int
    {
        if (! $event->club_id) {
            return 0;
        }
        $club = $event->club()->first();
        if (! $club) {
            return 0;
        }
        $followerIds = ClubFollower::query()
            ->where('club_id', $event->club_id)
            ->pluck('user_id')
            ->all();
        if (empty($followerIds)) {
            return 0;
        }
        $created = 0;
        foreach ($followerIds as $userId) {
            $notification = ClubNotification::firstOrCreate(
                [
                    'user_id' => $userId,
                    'club_id' => $club->id,
                    'event_id' => $event->id,
                    'type' => 'club_event_created',
                ],
                [
                    'title' => $club->name . ' posted a new event',
                    'message' => $club->name . ' posted a new event: ' . $event->name,
                    'metadata' => [
                        'club_name' => $club->name,
                        'event_name' => $event->name,
                        'event_date' => optional($event->date)->toDateString(),
                    ],
                    'is_read' => false,
                ]
            );
            if ($notification->wasRecentlyCreated) {
                $created++;
            }
        }
        Log::info('Club follower notifications generated', [
            'event_id' => $event->id,
            'club_id' => $club->id,
            'created_count' => $created,
        ]);
        return $created;
    }
}
