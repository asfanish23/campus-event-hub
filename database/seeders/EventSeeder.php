<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = [
            [
                'name' => 'Tech Talk 2024',
                'description' => 'A comprehensive session on the latest technology trends and innovations in the industry.',
                'date' => '2024-12-15',
                'start_time' => '14:00',
                'end_time' => '16:00',
                'location' => 'Main Auditorium',
                'category' => 'Technology',
                'status' => 'Upcoming',
                'expected_attendees' => 150,
            ],
            [
                'name' => 'AI Workshop',
                'description' => 'Hands-on workshop on artificial intelligence and machine learning applications.',
                'date' => '2024-12-20',
                'start_time' => '10:00',
                'end_time' => '12:00',
                'location' => 'Computer Lab 1',
                'category' => 'Workshop',
                'status' => 'Currently Running',
                'expected_attendees' => 80,
            ],
            [
                'name' => 'Career Fair',
                'description' => 'Meet industry professionals and explore career opportunities with leading companies.',
                'date' => '2024-11-28',
                'start_time' => '09:00',
                'end_time' => '17:00',
                'location' => 'Student Center',
                'category' => 'Career',
                'status' => 'Completed',
                'expected_attendees' => 200,
            ],
            [
                'name' => 'Hackathon 2024',
                'description' => 'A 24-hour coding competition where teams build innovative solutions.',
                'date' => '2024-12-25',
                'start_time' => '08:00',
                'end_time' => '08:00',
                'location' => 'Innovation Hub',
                'category' => 'Competition',
                'status' => 'Upcoming',
                'expected_attendees' => 120,
            ],
            [
                'name' => 'Networking Night',
                'description' => 'Casual networking event to connect with fellow tech enthusiasts.',
                'date' => '2024-12-01',
                'start_time' => '18:00',
                'end_time' => '20:00',
                'location' => 'Cafeteria',
                'category' => 'Social',
                'status' => 'Completed',
                'expected_attendees' => 100,
            ],
            [
                'name' => 'Web Development Bootcamp',
                'description' => 'Intensive bootcamp covering modern web development frameworks and best practices.',
                'date' => '2023-10-15',
                'start_time' => '09:00',
                'end_time' => '17:00',
                'location' => 'Room 201',
                'category' => 'Workshop',
                'status' => 'Completed',
                'expected_attendees' => 60,
            ],
            [
                'name' => 'Annual Tech Fest',
                'description' => 'Annual celebration of technology with exhibitions, competitions, and talks.',
                'date' => '2023-11-20',
                'start_time' => '10:00',
                'end_time' => '18:00',
                'location' => 'Convention Center',
                'category' => 'Technology',
                'status' => 'Completed',
                'expected_attendees' => 500,
            ],
            [
                'name' => 'Design Sprint',
                'description' => 'A fast-paced design workshop focusing on UX/UI principles.',
                'date' => '2024-12-28',
                'start_time' => '13:00',
                'end_time' => '16:00',
                'location' => 'Design Studio',
                'category' => 'Workshop',
                'status' => 'Upcoming',
                'expected_attendees' => 40,
            ],
        ];

        foreach ($events as $event) {
            Event::create($event);
        }
    }
}
