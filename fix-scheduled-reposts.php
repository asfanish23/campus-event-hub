<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Event;

// Find event with scheduled repost but without auto_repost flag
$events = Event::whereNotNull('instagram_repost_at')
    ->where('instagram_reposted', false)
    ->where('instagram_auto_repost', false)
    ->get();

foreach ($events as $event) {
    echo "Fixing Event {$event->id}: {$event->name}\n";
    $event->update(['instagram_auto_repost' => true]);
    echo "  ✅ Set instagram_auto_repost to true\n";
}

if ($events->count() === 0) {
    echo "No events to fix\n";
}
?>
