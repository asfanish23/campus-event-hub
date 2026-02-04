<?php

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reviews = [
            // Event 1 Reviews
            [
                'event_id' => 1,
                'reviewer_name' => 'Sarah Ahmad',
                'rating' => 5,
                'review_text' => 'Amazing event! Very informative and well organized.',
            ],
            [
                'event_id' => 1,
                'reviewer_name' => 'Mike Chen',
                'rating' => 4,
                'review_text' => 'Great content, need more time for Q&A session.',
            ],
            [
                'event_id' => 1,
                'reviewer_name' => 'Aisha Ibrahim',
                'rating' => 5,
                'review_text' => 'Well organized and very helpful for my career planning.',
            ],
            [
                'event_id' => 1,
                'reviewer_name' => 'John Tan',
                'rating' => 3,
                'review_text' => 'Good competition but venue was too crowded.',
            ],
            [
                'event_id' => 1,
                'reviewer_name' => 'Lisa Wong',
                'rating' => 5,
                'review_text' => 'One of the best events this year! Exceeded my expectations.',
            ],
            [
                'event_id' => 1,
                'reviewer_name' => 'David Kumar',
                'rating' => 4,
                'review_text' => 'Excellent speakers and interesting topics. Would attend again!',
            ],
            // Event 2 Reviews
            [
                'event_id' => 2,
                'reviewer_name' => 'Emma Johnson',
                'rating' => 5,
                'review_text' => 'Best hackathon I have participated in! Amazing prizes and mentors.',
            ],
            [
                'event_id' => 2,
                'reviewer_name' => 'Ahmad Hassan',
                'rating' => 4,
                'review_text' => 'Great learning experience. The judging criteria could be clearer though.',
            ],
            [
                'event_id' => 2,
                'reviewer_name' => 'Sophie Lee',
                'rating' => 5,
                'review_text' => 'Had so much fun collaborating with my team. Will definitely join next year!',
            ],
            // Event 3 Reviews
            [
                'event_id' => 3,
                'reviewer_name' => 'Marcus Bell',
                'rating' => 4,
                'review_text' => 'Informative workshop with practical examples. Very engaging!',
            ],
            [
                'event_id' => 3,
                'reviewer_name' => 'Fatima Al-Mazrouei',
                'rating' => 5,
                'review_text' => 'Excellent session! The instructor explained complex concepts clearly.',
            ],
            [
                'event_id' => 3,
                'reviewer_name' => 'Chen Wei',
                'rating' => 3,
                'review_text' => 'Good content but technical issues affected the presentation.',
            ],
            // Event 4 Reviews
            [
                'event_id' => 4,
                'reviewer_name' => 'Rachel Martinez',
                'rating' => 5,
                'review_text' => 'Inspiring tech talk! Great insights into latest industry trends.',
            ],
            [
                'event_id' => 4,
                'reviewer_name' => 'Ravi Patel',
                'rating' => 4,
                'review_text' => 'Very informative session. Wish we had more networking time.',
            ],
            // Event 5 Reviews
            [
                'event_id' => 5,
                'reviewer_name' => 'Jessica Brown',
                'rating' => 5,
                'review_text' => 'Amazing career fair! Met many great companies. Highly recommended!',
            ],
            [
                'event_id' => 5,
                'reviewer_name' => 'Anirudh Singh',
                'rating' => 4,
                'review_text' => 'Good opportunity to network. More time slots would be helpful.',
            ],
            // Event 6 Reviews
            [
                'event_id' => 6,
                'reviewer_name' => 'Olivia Grant',
                'rating' => 5,
                'review_text' => 'Fantastic event! Learned a lot about the latest tech trends.',
            ],
            [
                'event_id' => 6,
                'reviewer_name' => 'Nathan Drake',
                'rating' => 4,
                'review_text' => 'Great performances and informative sessions throughout the day.',
            ],
            [
                'event_id' => 6,
                'reviewer_name' => 'Maya Desai',
                'rating' => 5,
                'review_text' => 'The energy and enthusiasm were incredible! Loved every minute.',
            ],
            // Event 7 Reviews
            [
                'event_id' => 7,
                'reviewer_name' => 'Carlos Rodriguez',
                'rating' => 4,
                'review_text' => 'Comprehensive bootcamp with excellent hands-on training.',
            ],
            [
                'event_id' => 7,
                'reviewer_name' => 'Zainab Hassan',
                'rating' => 5,
                'review_text' => 'Life-changing experience! The instructors were very supportive.',
            ],
            [
                'event_id' => 7,
                'reviewer_name' => 'Tom Wilson',
                'rating' => 4,
                'review_text' => 'Very practical curriculum and great projects. Highly valuable.',
            ],
        ];

        foreach ($reviews as $review) {
            Review::create($review);
        }
    }
}
