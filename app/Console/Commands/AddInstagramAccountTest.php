<?php

namespace App\Console\Commands;

use App\Models\Club;
use App\Models\InstagramAccount;
use Illuminate\Console\Command;

class AddInstagramAccountTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'instagram:setup-test {club_id=1}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Setup test Instagram account for a club';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $clubId = $this->argument('club_id');
        $club = Club::find($clubId);

        if (!$club) {
            $this->error("Club with ID {$clubId} not found!");
            return Command::FAILURE;
        }

        $this->info("Setting up Instagram account for: {$club->name}\n");

        // Check if already exists
        if ($club->instagramAccount) {
            $this->warn("Club already has Instagram account configured!");
            return Command::FAILURE;
        }

        // Ask for details
        $username = $this->ask('Instagram business account username (or press Enter for test): ') ?: 'test_business_account';
        $businessId = $this->ask('Instagram business ID (or press Enter for test): ') ?: '12345678';
        $token = $this->ask('Instagram access token (or press Enter for test token): ') ?: 'test_access_token_' . uniqid();

        // Create Instagram account
        $instagramAccount = InstagramAccount::create([
            'club_id' => $club->id,
            'instagram_username' => $username,
            'instagram_business_id' => $businessId,
            'access_token' => $token, // Will be automatically encrypted by model
            'is_active' => true,
        ]);

        $this->info("\n✓ Instagram account created!");
        $this->line("Username: {$username}");
        $this->line("Business ID: {$businessId}");
        $this->line("Status: Active");

        $this->info("\nNow when you create events, they will attempt to post to Instagram!");
        $this->info("⚠️  Note: With test tokens, posting will fail.");
        $this->info("✓ For real posting, provide a valid Facebook Developer token.\n");

        return Command::SUCCESS;
    }
}
