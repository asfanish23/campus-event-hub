<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Event;

echo "Server now(): " . now()->format('Y-m-d H:i:s e') . "\n";
echo "Server timezone: " . date_default_timezone_get() . "\n\n";

$event = Event::find(8);
echo "Event 8 (Frisbee Skills Workshop):\n";
echo "  scheduled_at: " . $event->instagram_scheduled_at->format('Y-m-d H:i:s') . "\n";
echo "  Is scheduled_at in past? " . ($event->instagram_scheduled_at->isPast() ? 'YES' : 'NO') . "\n";
echo "  Difference in minutes: " . $event->instagram_scheduled_at->diffInMinutes(now()) . "\n";
?>
