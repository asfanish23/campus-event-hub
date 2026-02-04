<?php

namespace Database\Seeders;

use App\Models\Club;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClubSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Club::create([
            'name' => 'Technology Club',
            'email' => 'techclub@uitm.edu.my',
            'category' => 'Technology',
            'description' => 'Technology Club is dedicated to fostering innovation and technical skills among students. We organize workshops, hackathons, and tech talks featuring industry experts.',
            'president_name' => 'Ahmad Zaki',
            'president_contact' => '+60 12-345 6789',
            'facebook_url' => 'https://facebook.com/techclub',
            'instagram_url' => 'https://instagram.com/techclub',
            'twitter_url' => 'https://twitter.com/techclub',
            'total_members' => 120,
        ]);
    }
}
