#!/usr/bin/env php
<?php
/*
 * Quick script to add sample events to database for Telegram bot testing
 */

define('LARAVEL_START', microtime(true));

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Event;
use App\Models\Club;
use Carbon\Carbon;

try {
    // Ensure we have a club
    $club = Club::first();
    if (!$club) {
        echo "❌ Error: No club found. Please create a club first.\n";
        exit(1);
    }

    $now = Carbon::now();
    $count = 0;

    $events = [
        [
            'name' => 'Tech Workshop: Web Development 🖥️',
            'category' => 'Technology',
            'date' => $now->clone()->addDays(1)->toDateString(),
            'start_time' => '14:00',
            'end_time' => '16:00',
            'location' => 'Lab 101',
            'description' => 'Learn modern web development with Laravel and React',
            'status' => 'Upcoming',
        ],
        [
            'name' => 'Art Exhibition Opening 🎨',
            'category' => 'Arts',
            'date' => $now->clone()->addDays(2)->toDateString(),
            'start_time' => '18:00',
            'end_time' => '21:00',
            'location' => 'Campus Gallery',
            'description' => 'Showcase of student artwork and creative works',
            'status' => 'Upcoming',
        ],
        [
            'name' => 'Sports Day - Football Tournament ⚽',
            'category' => 'Sports',
            'date' => $now->clone()->addDays(3)->toDateString(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'location' => 'Main Stadium',
            'description' => 'Inter-departmental football tournament',
            'status' => 'Upcoming',
        ],
        [
            'name' => 'Networking Mixer with Alumni 🤝',
            'category' => 'Networking',
            'date' => $now->clone()->addDays(4)->toDateString(),
            'start_time' => '17:00',
            'end_time' => '19:00',
            'location' => 'Student Lounge',
            'description' => 'Meet successful alumni and build your professional network',
            'status' => 'Upcoming',
        ],
        [
            'name' => 'Music Concert - Campus Band 🎵',
            'category' => 'Music',
            'date' => $now->clone()->addDays(5)->toDateString(),
            'start_time' => '19:00',
            'end_time' => '21:30',
            'location' => 'Main Auditorium',
            'description' => 'Live performance by campus music band and local artists',
            'status' => 'Upcoming',
        ],
        [
            'name' => 'Innovation Hackathon 💡',
            'category' => 'Technology',
            'date' => $now->clone()->addDays(6)->toDateString(),
            'start_time' => '10:00',
            'end_time' => '18:00',
            'location' => 'Innovation Center',
            'description' => 'Build innovative solutions in 8 hours',
            'status' => 'Upcoming',
        ],
    ];

    foreach ($events as $eventData) {
        $exists = Event::where('name', $eventData['name'])->exists();
        if (!$exists) {
            Event::create([
                'club_id' => $club->id,
                ...$eventData
            ]);
            $count++;
            echo "✓ Created: {$eventData['name']}\n";
        } else {
            echo "⊘ Already exists: {$eventData['name']}\n";
        }
    }

    $total = Event::count();
    $categories = Event::distinct()->pluck('category')->filter()->toArray();

    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✓ SUCCESS!\n";
    echo "New events added: $count\n";
    echo "Total events in database: $total\n";
    echo "Available categories: " . implode(', ', $categories) . "\n";
    echo str_repeat("=", 50) . "\n";
    echo "\n🎉 Now try the bot again! Hit /start and click Preferences button.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    exit(1);
}
