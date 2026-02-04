<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Club;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TelegramBotEventsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a sample club
        $club = Club::firstOrCreate(
            ['id' => 1],
            [
                'club_name' => 'Campus Tech Club',
                'description' => 'For tech enthusiasts',
                'club_logo' => 'logo.png',
            ]
        );

        $now = Carbon::now();

        // Create sample events for this week with different categories
        $events = [
            [
                'name' => 'Tech Workshop: Web Development',
                'category' => 'Technology',
                'date' => $now->clone()->addDays(1)->toDateString(),
                'start_time' => '14:00',
                'end_time' => '16:00',
                'location' => 'Lab 101',
                'description' => 'Learn modern web development with Laravel and React',
                'status' => 'upcoming',
            ],
            [
                'name' => 'Art Exhibition Opening',
                'category' => 'Arts',
                'date' => $now->clone()->addDays(2)->toDateString(),
                'start_time' => '18:00',
                'end_time' => '21:00',
                'location' => 'Campus Gallery',
                'description' => 'Showcase of student artwork',
                'status' => 'upcoming',
            ],
            [
                'name' => 'Sports Day - Football Tournament',
                'category' => 'Sports',
                'date' => $now->clone()->addDays(3)->toDateString(),
                'start_time' => '09:00',
                'end_time' => '17:00',
                'location' => 'Main Stadium',
                'description' => 'Inter-departmental football tournament',
                'status' => 'upcoming',
            ],
            [
                'name' => 'Networking Mixer with Alumni',
                'category' => 'Networking',
                'date' => $now->clone()->addDays(4)->toDateString(),
                'start_time' => '17:00',
                'end_time' => '19:00',
                'location' => 'Student Lounge',
                'description' => 'Meet alumni and make professional connections',
                'status' => 'upcoming',
            ],
            [
                'name' => 'Music Concert - Campus Band',
                'category' => 'Music',
                'date' => $now->clone()->addDays(5)->toDateString(),
                'start_time' => '19:00',
                'end_time' => '21:30',
                'location' => 'Auditorium',
                'description' => 'Live performance by campus music band',
                'status' => 'upcoming',
            ],
            [
                'name' => 'Innovation Hackathon',
                'category' => 'Technology',
                'date' => $now->clone()->addDays(6)->toDateString(),
                'start_time' => '10:00',
                'end_time' => '18:00',
                'location' => 'Innovation Center',
                'description' => '24-hour hackathon for tech enthusiasts',
                'status' => 'upcoming',
            ],
        ];

        foreach ($events as $eventData) {
            Event::firstOrCreate(
                ['name' => $eventData['name']],
                array_merge($eventData, ['club_id' => $club->id])
            );
        }

        $this->command->info('Sample events created successfully!');
    }
}
