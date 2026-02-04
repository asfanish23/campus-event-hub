<?php

namespace App\Console\Commands;

use App\Models\Club;
use App\Models\InstagramAccount;
use Illuminate\Console\Command;

class SetupInstagramTestAccounts extends Command
{
    protected $signature = 'instagram:setup-test-accounts';
    protected $description = 'Setup test Instagram accounts for all clubs';

    public function handle()
    {
        $clubs = Club::all();

        foreach ($clubs as $club) {
            if ($club->instagramAccount) {
                $this->info("✓ {$club->name} already has Instagram account");
                continue;
            }

            InstagramAccount::create([
                'club_id' => $club->id,
                'instagram_username' => strtolower(str_replace(' ', '_', $club->name)) . '_official',
                'instagram_business_id' => '999' . str_pad($club->id, 10, '0', STR_PAD_LEFT),
                'access_token' => 'test_token_' . uniqid(),
                'is_active' => true,
            ]);

            $this->info("✓ Created Instagram account for {$club->name}");
        }

        $this->info("\n✓ All clubs now have test Instagram accounts!");
        $this->info("Run: php artisan instagram:debug\n");
    }
}
