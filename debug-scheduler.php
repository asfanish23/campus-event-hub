<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Event;

// Check server time
echo "Server now(): " . now()->format('Y-m-d H:i:s') . "\n";
echo "Database time: " . \Illuminate\Support\Facades\DB::select('SELECT NOW()')[0]->{'NOW()'} . "\n\n";

// Find the test event
$event = Event::find(7);
echo "Event 7:\n";
echo "  instagram_auto_post: " . ($event->instagram_auto_post ? 'TRUE' : 'FALSE') . "\n";
echo "  instagram_scheduled_at: " . ($event->instagram_scheduled_at ? $event->instagram_scheduled_at->format('Y-m-d H:i:s') : 'NULL') . "\n";
echo "  instagram_scheduled_posted: " . ($event->instagram_scheduled_posted ? 'TRUE' : 'FALSE') . "\n";
echo "  instagram_media_id: " . ($event->instagram_media_id ?? 'NULL') . "\n";

// Check if it passes each condition
echo "\nCondition checks:\n";
echo "  instagram_auto_post = true: " . ($event->instagram_auto_post ? '✅' : '❌') . "\n";
echo "  instagram_scheduled_posted = false: " . (!$event->instagram_scheduled_posted ? '✅' : '❌') . "\n";
echo "  instagram_scheduled_at NOT NULL: " . ($event->instagram_scheduled_at ? '✅' : '❌') . "\n";
echo "  instagram_media_id IS NULL: " . (!$event->instagram_media_id ? '✅' : '❌') . "\n";
echo "  instagram_scheduled_at <= now(): " . ($event->instagram_scheduled_at && $event->instagram_scheduled_at->lte(now()) ? '✅' : '❌') . "\n";

// Try the exact query
$ready = Event::where('instagram_auto_post', true)
    ->where('instagram_scheduled_posted', false)
    ->whereNotNull('instagram_scheduled_at')
    ->whereNull('instagram_media_id')
    ->where('instagram_scheduled_at', '<=', now())
    ->get();

echo "\nQuery result: " . $ready->count() . " events ready\n";
?>
