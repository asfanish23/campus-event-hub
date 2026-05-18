<?php

namespace App\Observers;

use App\Models\Event;
use App\Services\ClubNotificationService;

class EventObserver
{
    public function created(Event $event): void
    {
        app(ClubNotificationService::class)->notifyClubFollowers($event);
    }
}
