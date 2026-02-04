<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Event;
use Carbon\Carbon;

// Find the KETOK event
$event = Event::where('name', 'like', '%KETOK%')->first();

if(!$event) {
    echo "Event not found!\n";
    exit;
}

echo "Found event: {$event->name}\n";
echo "ID: {$event->id}\n";
echo "Image: " . ($event->event_image ?? 'NONE') . "\n";

// Schedule it
if(!$event->event_image) {
    echo "\n❌ Event has no image!\n";
    exit;
}

$scheduledTime = Carbon::now()->addMinutes(2);
$event->update([
    'instagram_auto_post' => true,
    'instagram_scheduled_at' => $scheduledTime,
    'instagram_scheduled_posted' => false,
]);

echo "\n✅ Scheduled at: " . $scheduledTime->format('Y-m-d H:i:s') . "\n";
echo "Ready? " . ($event->isReadyForScheduledInstagramPost() ? 'YES' : 'NO') . "\n";
