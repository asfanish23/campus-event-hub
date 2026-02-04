<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\TelegramBotService;
use Carbon\Carbon;

class SendTelegramWeeklyRecommendations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:send-weekly-recommendations {--time=09:00}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send weekly event recommendations to Telegram users based on their preferences';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $telegramService = app(TelegramBotService::class);
        $time = $this->option('time');

        $this->info("Sending weekly event recommendations at {$time}...");

        // Get all users with Telegram connected and notifications enabled
        $users = User::where('telegram_connected', true)
            ->whereHas('telegramPreference', function ($query) {
                $query->where('notifications_enabled', true);
            })
            ->with('telegramPreference')
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($users as $user) {
            try {
                // Check if it's the right time for this user
                $prefTime = $user->telegramPreference->notification_time ?? '09:00';
                $currentTime = Carbon::now()->format('H:i');

                if ($currentTime !== $prefTime) {
                    continue;
                }

                if ($telegramService->sendWeeklyRecommendations($user)) {
                    $sent++;
                    $this->line("✓ Sent recommendations to {$user->name}");
                } else {
                    $failed++;
                    $this->warn("✗ Failed to send recommendations to {$user->name}");
                }
            } catch (\Exception $e) {
                $failed++;
                $this->error("Error sending to {$user->name}: {$e->getMessage()}");
            }
        }

        $this->info("\n✅ Completed! Sent: {$sent}, Failed: {$failed}");
    }
}

