<?php

namespace App\Console\Commands;

use App\Models\Club;
use Illuminate\Console\Command;

class UpdateInactiveClubs extends Command
{
    protected $signature = 'clubs:update-inactive';

    protected $description = 'Deactivate clubs that have not shown activity for more than 90 days';

    public function handle(): int
    {
        $updated = Club::query()
            ->active()
            ->whereNotNull('last_activity_at')
            ->where('last_activity_at', '<', now()->subDays(90))
            ->update([
                'status' => Club::STATUS_INACTIVE,
                'updated_at' => now(),
            ]);

        $this->info("Marked {$updated} clubs as inactive.");

        return self::SUCCESS;
    }
}