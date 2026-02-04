<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;

class DebugInstagramEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'instagram:debug {limit=10}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Debug Instagram posting status for all events';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->argument('limit');
        
        $events = Event::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        $this->info("=== Recent Events Instagram Status ===\n");

        foreach ($events as $event) {
            $this->line("ID: {$event->id} | Name: {$event->name}");
            $this->line("  Posted to Instagram: " . ($event->isPostedToInstagram() ? '✅ YES' : '❌ NO'));
            $this->line("  Media ID: " . ($event->instagram_media_id ?? 'NULL'));
            $this->line("  Posted At: " . ($event->instagram_posted_at ?? 'NULL'));
            $this->line("  Last Synced: " . ($event->instagram_last_synced_at ?? 'NULL'));
            $this->line("  Metrics: Likes={$event->instagram_likes_count}, Comments={$event->instagram_comments_count}, Reach={$event->instagram_reach}");
            $this->line("");
        }

        // Check club Instagram account
        $this->info("=== Club Instagram Accounts ===\n");
        $clubs = \App\Models\Club::with('instagramAccount')->get();
        
        foreach ($clubs as $club) {
            $this->line("Club: {$club->name}");
            if ($club->instagramAccount) {
                $this->line("  ✅ Instagram Account Configured");
                $this->line("  Business ID: {$club->instagramAccount->instagram_business_id}");
                $this->line("  Token Valid: " . ($club->instagramAccount->isTokenValid() ? '✅ YES' : '❌ NO'));
                $this->line("  Token Expires: " . ($club->instagramAccount->token_expires_at ?? 'Never'));
            } else {
                $this->line("  ❌ No Instagram Account Configured");
            }
            $this->line("");
        }

        return Command::SUCCESS;
    }
}
