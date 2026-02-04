<?php

// Quick test file to check event Instagram data
require __DIR__ . '/bootstrap/app.php';

$app->make(\Illuminate\Contracts\Http\Kernel::class)->handle(
    $request = \Illuminate\Http\Request::capture()
);

use App\Models\Event;

$event = Event::find(8);

if ($event) {
    echo "=== Event Instagram Status ===\n";
    echo "Event ID: " . $event->id . "\n";
    echo "Event Name: " . $event->name . "\n";
    echo "Instagram Media ID: " . ($event->instagram_media_id ?? 'NULL') . "\n";
    echo "Is Posted: " . ($event->isPostedToInstagram() ? 'YES' : 'NO') . "\n";
    echo "Likes: " . $event->instagram_likes_count . "\n";
    echo "Comments: " . $event->instagram_comments_count . "\n";
    echo "Reach: " . $event->instagram_reach . "\n";
    echo "Impressions: " . $event->instagram_impressions . "\n";
    echo "Engagement Rate: " . $event->instagram_engagement_rate . "%\n";
} else {
    echo "Event not found\n";
}
