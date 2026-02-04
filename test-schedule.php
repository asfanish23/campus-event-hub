<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Event;

// Find an unposted event to test
$testEvent = Event::where('instagram_auto_post', false)
    ->where('event_image', '!=', null)
    ->whereNull('instagram_scheduled_at')
    ->first();

if (!$testEvent) {
    echo "No suitable test event found\n";
    exit;
}

$futureTime = now()->addMinutes(2);

echo "Scheduling Event {$testEvent->id} ({$testEvent->name}) for {$futureTime->format('Y-m-d H:i:s')}\n";

$testEvent->update([
    'instagram_auto_post' => true,
    'instagram_scheduled_at' => $futureTime,
    'instagram_scheduled_posted' => false,
]);

echo "✅ Event scheduled!\n";
echo "Now wait 2 minutes and the scheduler should process it automatically.\n";
echo "Or manually run: php artisan instagram:process-scheduled-posts\n";
?>
