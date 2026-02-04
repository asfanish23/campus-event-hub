<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Event;

// Check all scheduled events
$events = Event::whereNotNull('instagram_scheduled_at')
    ->where('instagram_scheduled_posted', false)
    ->get();

echo "Checking scheduled events...\n\n";

if($events->isEmpty()) {
    echo "No scheduled events found!\n";
} else {
    foreach($events as $event) {
        echo "Event ID: {$event->id}\n";
        echo "Name: {$event->name}\n";
        echo "Scheduled At: {$event->instagram_scheduled_at}\n";
        echo "Is Ready? " . ($event->isReadyForScheduledInstagramPost() ? 'YES' : 'NO') . "\n";
        echo "Club ID: {$event->club_id}\n";
        echo "Has Image? " . ($event->image ? 'YES' : 'NO') . "\n";
        echo "---\n";
    }
}
