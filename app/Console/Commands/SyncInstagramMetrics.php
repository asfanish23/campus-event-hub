<?php

namespace App\Console\Commands;

use App\Services\InstagramSyncService;
use Illuminate\Console\Command;
use Exception;

class SyncInstagramMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'instagram:sync-metrics {--force : Force sync regardless of last sync time}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Synchronize Instagram engagement metrics for all posted events';

    private InstagramSyncService $syncService;

    /**
     * Create a new command instance.
     */
    public function __construct(InstagramSyncService $syncService)
    {
        parent::__construct();
        $this->syncService = $syncService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Instagram metrics synchronization...');
        
        try {
            // Sync all event metrics
            $results = $this->syncService->syncAllEventMetrics();

            // Display results
            $this->info('Synchronization Complete!');
            $this->newLine();
            $this->info("Total events synced: {$results['total_synced']}");
            $this->info("Successful: {$results['successful']}");
            
            if ($results['failed'] > 0) {
                $this->warn("Failed: {$results['failed']}");
                
                if (!empty($results['errors'])) {
                    $this->newLine();
                    $this->warn('Errors:');
                    foreach ($results['errors'] as $error) {
                        $eventId = $error['event_id'] ?? 'Unknown';
                        $errorMsg = $error['error'] ?? 'Unknown error';
                        $this->error("  - Event ID {$eventId}: {$errorMsg}");
                    }
                }
            }

            // Get and display sync status
            $status = $this->syncService->getSyncStatus();
            
            $this->newLine();
            $this->info('Current Status:');
            $this->line("  Total posted events: {$status['total_posted_events']}");
            $this->line("  Recently synced (last hour): {$status['recently_synced']}");
            $this->line("  Needs sync: {$status['needs_sync']}");

            if (!empty($status['top_engaged_events'])) {
                $this->newLine();
                $this->info('Top Engaged Events:');
                foreach ($status['top_engaged_events'] as $event) {
                    $this->line("  - {$event['name']}: {$event['instagram_engagement_rate']}% engagement ({$event['instagram_likes_count']} likes, {$event['instagram_comments_count']} comments)");
                }
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error during synchronization: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
