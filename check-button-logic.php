<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Event;

$events = Event::with('club')->whereIn('id', [7, 8, 9])->get();

foreach($events as $e) {
    echo "{$e->id}: {$e->name}\n";
    echo "  instagram_auto_post: " . ($e->instagram_auto_post ? "TRUE" : "FALSE") . "\n";
    echo "  instagram_media_id: " . ($e->instagram_media_id ?? "NULL") . "\n";
    echo "  Show Post Now: " . ((!$e->isPostedToInstagram() && !$e->instagram_auto_post) ? "YES" : "NO") . "\n";
    echo "  Show Schedule Post: " . ((!$e->isPostedToInstagram() && !$e->instagram_auto_post) ? "YES" : "NO") . "\n";
    echo "  Show Cancel Schedule: " . (($e->instagram_auto_post && $e->instagram_scheduled_at && !$e->instagram_scheduled_posted) ? "YES" : "NO") . "\n";
    echo "  Show Repost: " . ($e->isPostedToInstagram() ? "YES" : "NO") . "\n";
    echo "\n";
}
?>
