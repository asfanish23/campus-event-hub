<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use App\Models\TelegramUserPreference;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Carbon\Carbon;

class TelegramBotService
{
    protected $botToken;
    protected $apiUrl;
    protected $client;
    protected $categories = [
        'Academic',
        'Sports', 
        'Culture',
        'Technology',
        'Volunteer',
        'Leadership',
        'Religious',
        'Entrepreneurship',
        'Arts & Media',
        'Others',
    ];
    
    protected $notificationTimes = [
        '08:00' => '🌅 08:00',
        '09:00' => '09:00',
        '10:00' => '10:00',
        '12:00' => '☀️ 12:00', 
        '14:00' => '14:00',
        '16:00' => '16:00',
        '18:00' => '🌙 18:00',
        '20:00' => '20:00',
        '21:00' => '21:00',
    ];

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->apiUrl = config('services.telegram.api_url');
        $this->client = new Client();
        
        // Set up persistent menu
        $this->setMainMenu();
    }

    /**
     * Set up persistent bottom menu
     */
    protected function setMainMenu(): void
    {
        try {
            $this->client->post(
                "{$this->apiUrl}/bot{$this->botToken}/setMyCommands",
                ['json' => [
                    'commands' => [
                        ['command' => 'start', 'description' => '🏠 Home'],
                        ['command' => 'thisweek', 'description' => '📅 This Week'],
                        ['command' => 'preferences', 'description' => '⚙️ Preferences'],
                        ['command' => 'help', 'description' => '❓ Help'],
                    ]
                ]]
            );
        } catch (GuzzleException $e) {
            Log::warning('Failed to set main menu', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Process incoming webhook update
     */
    public function handleUpdate(array $update): void
    {
        try {
            if (isset($update['message'])) {
                $this->handleMessage($update['message']);
            } elseif (isset($update['callback_query'])) {
                $this->handleCallbackQuery($update['callback_query']);
            }
        } catch (\Exception $e) {
            Log::error('Telegram update handling failed', [
                'error' => $e->getMessage(),
                'update' => $update
            ]);
        }
    }

    /**
     * Handle incoming message
     */
    protected function handleMessage(array $message): void
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';
        $userId = $message['from']['id'] ?? null;

        if (strpos($text, '/') === 0) {
            $this->handleCommand($chatId, $text, $userId);
        } else {
            $this->handleRegularMessage($chatId, $text);
        }
    }

    /**
     * Handle command messages
     */
    protected function handleCommand(int $chatId, string $text, ?int $userId): void
    {
        $command = strtolower(explode(' ', $text)[0]);
        
        $handlers = [
            '/start' => 'commandStart',
            '/menu' => 'commandMenu',
            '/subscribe' => 'commandSubscribe',
            '/preferences' => 'commandPreferences',
            '/thisweek' => 'commandThisWeek',
            '/help' => 'commandHelp',
            '/unsubscribe' => 'commandUnsubscribe',
        ];

        if (isset($handlers[$command])) {
            $method = $handlers[$command];
            $this->$method($chatId, $userId);
        } else {
            $this->sendMessage(
                $chatId,
                "❓ Unknown command. Type /help to see available commands."
            );
        }
    }

    /**
     * Handle callback queries from inline buttons
     */
    protected function handleCallbackQuery(array $callbackQuery): void
    {
        $chatId = $callbackQuery['message']['chat']['id'];
        $data = $callbackQuery['data'];
        $queryId = $callbackQuery['id'];

        $this->answerCallbackQuery($queryId);

        $handlers = [
            'week_events' => fn() => $this->commandThisWeek($chatId),
            'set_preferences' => fn() => $this->commandPreferences($chatId),
            'notification_settings' => fn() => $this->showNotificationSettings($chatId),
            'help' => fn() => $this->commandHelp($chatId),
            'toggle_notif' => fn() => $this->toggleNotifications($chatId),
            'set_notif_time' => fn() => $this->showNotificationTimeOptions($chatId),
            'show_menu' => fn() => $this->showMainMenu($chatId),
        ];

        // Handle preference toggling
        if (strpos($data, 'pref_') === 0) {
            $category = urldecode(substr($data, 5));
            $this->togglePreference($chatId, $category);
            return;
        }

        // Handle notification time selection
        if (strpos($data, 'notif_time_') === 0) {
            $time = substr($data, 11);
            $this->setNotificationTime($chatId, $time);
            return;
        }

        // Execute handler if exists
        if (isset($handlers[$data])) {
            $handlers[$data]();
        }
    }

    /**
     * Send message to Telegram
     */
    public function sendMessage(
        int $chatId, 
        string $text, 
        string $parseMode = 'HTML', 
        ?array $replyMarkup = null
    ): bool {
        try {
            $payload = [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => $parseMode,
            ];

            if ($replyMarkup) {
                $payload['reply_markup'] = $replyMarkup;
            }

            $this->client->post(
                "{$this->apiUrl}/bot{$this->botToken}/sendMessage",
                ['json' => $payload]
            );

            return true;
        } catch (GuzzleException $e) {
            Log::error('Telegram message sending failed', [
                'chat_id' => $chatId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Answer callback query
     */
    protected function answerCallbackQuery(string $queryId): void
    {
        try {
            $this->client->post(
                "{$this->apiUrl}/bot{$this->botToken}/answerCallbackQuery",
                ['json' => ['callback_query_id' => $queryId]]
            );
        } catch (GuzzleException $e) {
            Log::warning('Failed to answer callback query', [
                'query_id' => $queryId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Show main menu
     */
    protected function showMainMenu(int $chatId, string $greeting = ''): void
    {
        $text = $greeting ?: "📋 <b>Main Menu</b>\n\nWhat would you like to do?";
        
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '📅 This Week Events', 'callback_data' => 'week_events'],
                    ['text' => '⚙️ Preferences', 'callback_data' => 'set_preferences'],
                ],
                [
                    ['text' => '🔔 Notifications', 'callback_data' => 'notification_settings'],
                    ['text' => '❓ Help', 'callback_data' => 'help'],
                ],
            ],
        ];

        $this->sendMessage($chatId, $text, 'HTML', $keyboard);
    }

    /**
     * /start command
     */
    protected function commandStart(int $chatId, ?int $userId): void
    {
        $preference = TelegramUserPreference::firstOrCreate(
            ['telegram_chat_id' => $chatId],
            [
                'user_id' => User::where('telegram_chat_id', $chatId)->first()?->id,
                'notifications_enabled' => true,
            ]
        );

        $greeting = $preference->wasRecentlyCreated 
            ? "🎉 Welcome to CampusEventHub Bot!\n\nI'm here to help you discover events you'll actually care about 👀"
            : "🎉 Welcome back to CampusEventHub Bot!\n\nReady to discover more events?";

        $this->showMainMenu($chatId, $greeting);
    }

    /**
     * /menu command
     */
    protected function commandMenu(int $chatId, ?int $userId = null): void
    {
        $this->showMainMenu($chatId);
    }

    /**
     * /subscribe command
     */
    protected function commandSubscribe(int $chatId, ?int $userId = null): void
    {
        $preference = TelegramUserPreference::firstOrCreate(['telegram_chat_id' => $chatId]);
        $preference->update(['notifications_enabled' => true]);

        $this->sendMessage(
            $chatId,
            "✅ Notifications enabled!\n\nYou'll receive updates about events matching your preferences."
        );
    }

    /**
     * /unsubscribe command
     */
    protected function commandUnsubscribe(int $chatId, ?int $userId = null): void
    {
        $preference = TelegramUserPreference::where('telegram_chat_id', $chatId)->first();

        if (!$preference) {
            $this->sendMessage(
                $chatId,
                "ℹ️ You don't have preferences set up yet.\nUse /preferences to get started."
            );
            return;
        }

        $preference->update(['notifications_enabled' => false]);

        $this->sendMessage(
            $chatId,
            "✅ Notifications disabled.\n\nUse /subscribe to re-enable them anytime."
        );
    }

    /**
     * /preferences command
     */
    protected function commandPreferences(int $chatId, ?int $userId = null): void
    {
        $preference = TelegramUserPreference::firstOrCreate(['telegram_chat_id' => $chatId]);
        $currentPreferences = $preference->category_preferences ?? [];

        $text = "📌 <b>Select Your Preferences</b>\n\n" .
                "Choose which event categories interest you.\n" .
                "Click the same category again to remove it.\n\n";

        if (!empty($currentPreferences)) {
            $text .= "<b>✅ Current Preferences:</b>\n" .
                    implode(", ", $currentPreferences) . "\n\n";
        } else {
            $text .= "<i>No preferences selected yet</i>\n\n";
        }

        $text .= "<b>Available Categories:</b>";

        $keyboard = ['inline_keyboard' => []];

        foreach ($this->categories as $category) {
            $isSelected = in_array($category, $currentPreferences);
            $prefix = $isSelected ? '✅ ' : '';
            $keyboard['inline_keyboard'][] = [
                ['text' => $prefix . $category, 'callback_data' => 'pref_' . urlencode($category)]
            ];
        }

        $this->sendMessage($chatId, $text, 'HTML', $keyboard);
    }

    /**
     * Toggle preference category
     */
    protected function togglePreference(int $chatId, string $category): void
    {
        $preference = TelegramUserPreference::firstOrCreate(['telegram_chat_id' => $chatId]);
        $categories = $preference->category_preferences ?? [];

        if (in_array($category, $categories)) {
            $categories = array_values(array_diff($categories, [$category]));
            $message = "❌ Removed <b>{$category}</b> from preferences.";
        } else {
            $categories[] = $category;
            $message = "✅ Added <b>{$category}</b> to preferences.";
        }

        $preference->update(['category_preferences' => $categories]);

        // Show updated preferences
        $this->commandPreferences($chatId);
    }

    /**
     * Show notification settings
     */
    protected function showNotificationSettings(int $chatId): void
    {
        $preference = TelegramUserPreference::where('telegram_chat_id', $chatId)->first();

        if (!$preference) {
            $this->sendMessage($chatId, "No preferences set up yet. Use /preferences to set them.");
            return;
        }

        $status = $preference->notifications_enabled ? '🟢 Enabled' : '🔴 Disabled';
        
        $text = "<b>🔔 Notification Settings</b>\n\n" .
                "Status: {$status}\n" .
                "Time: {$preference->notification_time}\n" .
                "Event updates: " . ($preference->send_event_updates ? '✅' : '❌');

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => 'Toggle Notifications', 'callback_data' => 'toggle_notif'],
                    ['text' => 'Change Time', 'callback_data' => 'set_notif_time'],
                ],
                [
                    ['text' => '📋 Menu', 'callback_data' => 'show_menu'],
                ],
            ],
        ];

        $this->sendMessage($chatId, $text, 'HTML', $keyboard);
    }

    /**
     * Show notification time options
     */
    protected function showNotificationTimeOptions(int $chatId): void
    {
        $preference = TelegramUserPreference::where('telegram_chat_id', $chatId)->first();
        $currentTime = $preference?->notification_time ?? null;

        $text = "🕐 <b>When should we notify you?</b>\n\n";
        if ($currentTime) {
            $text .= "Current time: <b>{$currentTime}</b>\n\n";
        }

        $keyboard = ['inline_keyboard' => []];
        $row = [];
        
        foreach ($this->notificationTimes as $time => $label) {
            $isCurrent = $currentTime === $time;
            $displayText = ($isCurrent ? '✅ ' : '') . $label;
            
            $row[] = ['text' => $displayText, 'callback_data' => 'notif_time_' . $time];
            
            if (count($row) === 3) {
                $keyboard['inline_keyboard'][] = $row;
                $row = [];
            }
        }
        
        if (!empty($row)) {
            $keyboard['inline_keyboard'][] = $row;
        }

        $this->sendMessage($chatId, $text, 'HTML', $keyboard);
    }

    /**
     * Set notification time
     */
    protected function setNotificationTime(int $chatId, string $time): void
    {
        $preference = TelegramUserPreference::firstOrCreate(['telegram_chat_id' => $chatId]);
        $preference->update(['notification_time' => $time]);

        $this->sendMessage($chatId, "✅ Notification time set to <b>{$time}</b>");
        
        // Return to notification settings
        sleep(1);
        $this->showNotificationSettings($chatId);
    }

    /**
     * Toggle notifications
     */
    protected function toggleNotifications(int $chatId): void
    {
        $preference = TelegramUserPreference::where('telegram_chat_id', $chatId)->first();

        if (!$preference) {
            $this->sendMessage($chatId, "No preferences set up yet. Use /preferences to set them.");
            return;
        }

        $newStatus = !$preference->notifications_enabled;
        $preference->update(['notifications_enabled' => $newStatus]);

        $message = $newStatus 
            ? "✅ Notifications <b>enabled</b>! You'll receive updates at {$preference->notification_time}"
            : "✅ Notifications <b>disabled</b>. You won't receive notifications.";

        $this->sendMessage($chatId, $message);
    }

    /**
     * /thisweek command
     */
    protected function commandThisWeek(int $chatId, ?int $userId = null): void
    {
        $now = Carbon::now();
        $weekEnd = Carbon::now()->addDays(7);

        $events = Event::whereBetween('date', [$now->toDateString(), $weekEnd->toDateString()])
            ->where('status', 'upcoming')
            ->orderBy('date', 'asc')
            ->get();

        if ($events->isEmpty()) {
            $this->sendMessage($chatId, "📭 No events this week yet.\n\nCheck back later!");
            return;
        }

        // Filter by preferences if available
        $preference = TelegramUserPreference::where('telegram_chat_id', $chatId)->first();
        if ($preference && !empty($preference->category_preferences)) {
            $events = $events->filter(function ($event) use ($preference) {
                return in_array($event->category, $preference->category_preferences);
            });
            
            if ($events->isEmpty()) {
                $this->sendMessage(
                    $chatId, 
                    "📭 No events match your preferences this week.\n\nTry adjusting your preferences with /preferences"
                );
                return;
            }
        }

        $this->sendEventList($chatId, $events, "📅 <b>Events This Week</b>");
    }

    /**
     * Send formatted event list
     */
    protected function sendEventList(int $chatId, $events, string $title): void
    {
        $messages = [$title];
        
        foreach ($events as $event) {
            $eventText = sprintf(
                "\n\n<b>%s</b>\n" .
                "📍 %s\n" .
                "📅 %s • 🕐 %s\n" .
                "👥 %d attendees expected\n" .
                "🏷️ %s",
                $event->name,
                $event->location,
                $event->date->format('M d, Y'),
                $event->start_time?->format('H:i') ?? 'TBA',
                $event->expected_attendees ?? 0,
                $event->category
            );

            // If message gets too long, send current and start new
            if (strlen($messages[count($messages) - 1] . $eventText) > 4000) {
                $this->sendMessage($chatId, $messages[count($messages) - 1], 'HTML');
                $messages[] = "<b>📅 More Events</b>" . $eventText;
            } else {
                $messages[count($messages) - 1] .= $eventText;
            }
        }

        foreach ($messages as $message) {
            if (!empty(trim(strip_tags($message)))) {
                $this->sendMessage($chatId, $message, 'HTML');
                usleep(300000); // Small delay between messages
            }
        }
    }

    /**
     * /help command
     */
    protected function commandHelp(int $chatId, ?int $userId = null): void
    {
        $text = "<b>� Available Commands</b>\n\n" .
                "/start - Start the bot\n" .
                "/menu - Show main menu\n" .
                "/thisweek - View events this week\n" .
                "/preferences - Set event preferences\n" .
                "/subscribe - Enable notifications\n" .
                "/unsubscribe - Disable notifications\n" .
                "/help - Show this help message\n\n" .
                "<b>💡 Quick Tips:</b>\n" .
                "• Set your preferences to get personalized events\n" .
                "• Enable notifications to get daily updates\n" .
                "• Choose your notification time in settings";

        $this->sendMessage($chatId, $text, 'HTML');
    }

    /**
     * Handle regular (non-command) messages
     */
    protected function handleRegularMessage(int $chatId, string $text): void
    {
        $this->sendMessage(
            $chatId,
            "I'm here to help you discover campus events! 🎉\n\n" .
            "Use /menu to see what I can do."
        );
    }

    /**
     * Send scheduled notifications to users at their preferred times
     * Should be called hourly via Laravel Scheduler
     */
    public function sendScheduledNotifications(): void
    {
        $currentTime = now()->format('H:00');

        $preferences = TelegramUserPreference::where('notifications_enabled', true)
            ->where('notification_time', $currentTime)
            ->get();

        foreach ($preferences as $pref) {
            $this->sendPersonalizedDigest($pref);
        }
    }

    /**
     * Send personalized event digest based on user's preferences
     */
    protected function sendPersonalizedDigest(TelegramUserPreference $preference): void
    {
        $chatId = $preference->telegram_chat_id;
        $interests = $preference->category_preferences ?? [];

        // Only send notifications if user has set preferences
        if (empty($interests)) {
            return;
        }

        $query = Event::where('date', '>=', now()->toDateString())
            ->where('date', '<=', now()->addDays(2)->toDateString())
            ->where('status', 'upcoming')
            ->whereIn('category', $interests);

        $events = $query->orderBy('date', 'asc')->get();

        if ($events->isEmpty()) {
            return;
        }

        $text = "🔔 <b>Your Personalized Event Update</b>\n";
        $text .= "Based on your interests: <b>" . implode(', ', $interests) . "</b>\n\n";

        foreach ($events as $event) {
            $text .= "• <b>" . $event->name . "</b>\n";
            $text .= "🕒 " . $event->date->format('M d') . " at " . ($event->start_time?->format('H:i') ?? 'TBA') . "\n\n";
        }

        $this->sendMessage($chatId, $text, 'HTML');
    }

    /**
     * Send weekly recommendations to user
     */
    public function sendWeeklyRecommendations(User $user): bool
    {
        if (!$user->telegram_connected || !$user->telegram_chat_id) {
            return false;
        }

        $preference = $user->telegramPreference;
        if (!$preference || !$preference->notifications_enabled) {
            return false;
        }

        $now = Carbon::now();
        $endDate = $now->copy()->addDays($preference->days_in_advance ?? 7);

        $events = Event::whereBetween('date', [$now->toDateString(), $endDate->toDateString()])
            ->where('status', 'upcoming')
            ->when($preference->category_preferences, function ($query) use ($preference) {
                $query->whereIn('category', $preference->category_preferences);
            })
            ->orderBy('date', 'asc')
            ->get();

        if ($events->isEmpty()) {
            return $this->sendMessage(
                $user->telegram_chat_id,
                "📭 No events matching your preferences this week."
            );
        }

        $this->sendEventList($user->telegram_chat_id, $events, "📅 <b>Your Weekly Recommendations</b>");
        return true;
    }

    /**
     * Send notification to user
     */
    public function sendNotification(int $userId, string $title, string $message): bool
    {
        $user = User::find($userId);
        
        if (!$user || !$user->telegram_chat_id) {
            return false;
        }

        $text = "<b>" . htmlspecialchars($title) . "</b>\n\n" .
                htmlspecialchars($message);

        return $this->sendMessage($user->telegram_chat_id, $text);
    }

    /**
     * Get bot info
     */
    public function getBotInfo(): ?array
    {
        try {
            $response = $this->client->get("{$this->apiUrl}/bot{$this->botToken}/getMe");
            return json_decode($response->getBody(), true)['result'] ?? null;
        } catch (GuzzleException $e) {
            Log::error('Failed to get bot info', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
