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

// Schedule untuk sekarang (sudah past)
$scheduledTime = Carbon::now()->subMinute();
$event->update([
    'instagram_auto_post' => true,
    'instagram_scheduled_at' => $scheduledTime,
    'instagram_scheduled_posted' => false,
]);

echo "✅ Scheduled at: " . $scheduledTime->format('Y-m-d H:i:s') . "\n";
echo "Is past? " . ($scheduledTime->isPast() ? 'YES' : 'NO') . "\n";
echo "Ready? " . ($event->isReadyForScheduledInstagramPost() ? 'YES' : 'NO') . "\n";
