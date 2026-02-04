<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Event;

// Check all events
$events = Event::limit(5)->get();

echo "Total events in database: " . Event::count() . "\n\n";

foreach($events as $event) {
    echo "Event ID: {$event->id}\n";
    echo "Name: {$event->name}\n";
    echo "Instagram Auto Post: " . ($event->instagram_auto_post ?? 'NULL') . "\n";
    echo "Scheduled At: " . ($event->instagram_scheduled_at ?? 'NULL') . "\n";
    echo "Scheduled Posted: " . ($event->instagram_scheduled_posted ?? 'NULL') . "\n";
    echo "---\n";
}
