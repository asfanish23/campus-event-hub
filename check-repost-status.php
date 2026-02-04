<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Event;

$events = Event::with('club')->limit(10)->get();

foreach($events as $e) {
    echo "{$e->id}: {$e->name}\n";
    echo "  Posted to IG: " . ($e->instagram_media_id ? "YES (ID: {$e->instagram_media_id})" : "NO") . "\n";
    echo "  Has Image: " . ($e->event_image ? "YES ({$e->event_image})" : "NO") . "\n";
    echo "  Can Repost: " . ($e->isPostedToInstagram() ? "YES" : "NO") . "\n";
    echo "\n";
}
?>
