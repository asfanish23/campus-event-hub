<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\InstagramActivityNotification;
use Illuminate\Console\Command;

class AddInstagramTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'instagram:add-test-data {event_id=8}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Add test Instagram data to an event for demonstration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $eventId = $this->argument('event_id');
        $event = Event::find($eventId);

        if (!$event) {
            $this->error("Event with ID {$eventId} not found!");
            return Command::FAILURE;
        }

        $this->info("Adding test Instagram data to: {$event->name}");

        // Add Instagram data
        $event->update([
            'instagram_media_id' => 'test_' . uniqid(),
            'instagram_posted_at' => now()->subHours(2),
            'instagram_last_synced_at' => now()->subMinutes(30),
            'instagram_likes_count' => 45,
            'instagram_comments_count' => 12,
            'instagram_reach' => 280,
            'instagram_impressions' => 450,
            'instagram_engagement_rate' => 12.67,
        ]);

        $this->info("✓ Added Instagram metrics to event");

        // Create test notifications
        InstagramActivityNotification::create([
            'event_id' => $event->id,
            'club_id' => $event->club_id,
            'activity_type' => 'post_created',
            'message' => "✨ Your event \"{$event->name}\" was successfully posted to Instagram!",
        ]);

        InstagramActivityNotification::create([
            'event_id' => $event->id,
            'club_id' => $event->club_id,
            'activity_type' => 'engagement_milestone',
            'milestone_value' => 25,
            'milestone_label' => '25',
            'message' => "🎉 Your post reached 25 total interactions (likes + comments)!",
        ]);

        InstagramActivityNotification::create([
            'event_id' => $event->id,
            'club_id' => $event->club_id,
            'activity_type' => 'reach_milestone',
            'milestone_value' => 250,
            'milestone_label' => '250',
            'message' => "📈 Your post has reached 250 people on Instagram!",
        ]);

        $this->info("✓ Created 3 test notifications");
        $this->info("\nNow refresh your browser to see the changes!");
        $this->info("Dashboard: You should see Instagram Activity widget");
        $this->info("Event Detail: You should see Instagram Stats sidebar");

        return Command::SUCCESS;
    }
}
