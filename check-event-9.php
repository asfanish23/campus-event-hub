<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Event;

$event = Event::find(9);
echo "Event 9 full details:\n";
echo json_encode([
    'id' => $event->id,
    'name' => $event->name,
    'instagram_auto_post' => $event->instagram_auto_post,
    'instagram_scheduled_at' => $event->instagram_scheduled_at,
    'instagram_scheduled_posted' => $event->instagram_scheduled_posted,
    'instagram_media_id' => $event->instagram_media_id,
], JSON_PRETTY_PRINT);
?>
