<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Event;

// Schedule for 10 minutes AGO (in the past, so it should be processed immediately)
$pastTime = now()->subMinutes(10);

$event = Event::find(7);
echo "Rescheduling Event 7 to past time: $pastTime\n";

$event->update([
    'instagram_scheduled_at' => $pastTime,
    'instagram_scheduled_posted' => false,
    'instagram_media_id' => null, // Reset if it was posted
]);

echo "✅ Event 7 rescheduled to " . $pastTime->format('Y-m-d H:i:s') . "\n";
echo "Now run: php artisan instagram:process-scheduled-posts\n";
?>
