<?php

namespace App\Console\Commands;

use App\Services\ScheduledInstagramPostService;
use Illuminate\Console\Command;

class ProcessScheduledInstagramPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'instagram:process-scheduled-posts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and post scheduled Instagram events that are ready to be posted';

    /**
     * The scheduled Instagram post service.
     */
    private ScheduledInstagramPostService $scheduledPostService;

    /**
     * Create a new command instance.
     */
    public function __construct(ScheduledInstagramPostService $scheduledPostService)
    {
        parent::__construct();
        $this->scheduledPostService = $scheduledPostService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $verbose = $this->getOutput()->isVerbose();

        $this->info('🔄 Starting scheduled Instagram posts processing...');

        if ($verbose) {
            $this->line('Verbose mode enabled');
        }

        $results = $this->scheduledPostService->processScheduledPosts();

        // Display results
        $this->newLine();
        $this->info('📊 Processing Results:');
        $this->line("✅ Successfully posted: {$results['success']}");
        $this->line("❌ Failed: {$results['failed']}");
        $this->line("⏭️  Skipped: {$results['skipped']}");

        if (!empty($results['errors']) && $verbose) {
            $this->newLine();
            $this->warn('⚠️  Errors:');
            foreach ($results['errors'] as $error) {
                $this->line("  Event #{$error['event_id']}: {$error['error']}");
            }
        }

        $this->newLine();

        if ($results['failed'] === 0 && $results['success'] >= 0) {
            $this->info('✨ All scheduled posts processed successfully!');
            return Command::SUCCESS;
        }

        if ($results['failed'] > 0) {
            $this->warn('⚠️  Some posts failed. Check logs for more details.');
            return Command::FAILURE;
        }

        $this->info('ℹ️  No posts ready to process.');
        return Command::SUCCESS;
    }
}
