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

// Schedule untuk 1 minit dari sekarang
$scheduledTime = Carbon::now()->addMinutes(1);
$event->update([
    'instagram_auto_post' => true,
    'instagram_scheduled_at' => $scheduledTime,
    'instagram_scheduled_posted' => false,
]);

echo "✅ Scheduled at: " . $scheduledTime->format('Y-m-d H:i:s') . "\n";
echo "\nWaiting 65 seconds...\n";
sleep(65);

echo "\n🔄 Running scheduler...\n";
exec('/usr/bin/php /var/www/campus-event-hub/artisan instagram:process-scheduled-posts -v', $output);
foreach($output as $line) {
    echo "$line\n";
}
