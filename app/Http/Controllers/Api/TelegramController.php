<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TelegramBotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramBotService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Handle webhook updates from Telegram
     * POST /api/telegram/webhook
     */
    public function webhook(Request $request)
    {
        try {
            $update = $request->all();

            Log::info('Telegram webhook received', [
                'update_id' => $update['update_id'] ?? null,
                'message_type' => isset($update['message']) ? 'message' : (isset($update['callback_query']) ? 'callback' : 'unknown'),
            ]);

            $this->telegramService->handleUpdate($update);

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            Log::error('Telegram webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['ok' => true]); // Always return 200 to Telegram
        }
    }

    /**
     * Link user to Telegram
     * POST /api/telegram/link
     * Requires: telegram_chat_id, telegram_username
     */
    public function linkAccount(Request $request)
    {
        $request->validate([
            'telegram_chat_id' => 'required|string',
            'telegram_username' => 'nullable|string',
        ]);

        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Update user with Telegram info
        $user->update([
            'telegram_chat_id' => $request->telegram_chat_id,
            'telegram_connected' => true,
        ]);

        // Create or update preferences
        $user->telegramPreference()->updateOrCreate(
            ['user_id' => $user->id],
            ['notifications_enabled' => true]
        );

        // Send welcome message
        $this->telegramService->sendMessage(
            $request->telegram_chat_id,
            "✅ Your account is now linked!\n\n" .
            "Use /preferences to set your event interests.\n" .
            "Use /menu for more options."
        );

        return response()->json([
            'message' => 'Telegram account linked successfully',
            'user' => $user,
        ]);
    }

    /**
     * Get user's preferences
     * GET /api/telegram/preferences
     */
    public function getPreferences(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!$user->telegram_connected) {
            return response()->json([
                'linked' => false,
                'message' => 'Telegram account not linked',
            ]);
        }

        $preferences = $user->telegramPreference;

        return response()->json([
            'linked' => true,
            'preferences' => $preferences,
        ]);
    }

    /**
     * Update user preferences
     * PUT /api/telegram/preferences
     */
    public function updatePreferences(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!$user->telegram_connected) {
            return response()->json(['error' => 'Telegram not connected'], 400);
        }

        $validated = $request->validate([
            'category_preferences' => 'nullable|array',
            'category_preferences.*' => 'string',
            'notifications_enabled' => 'nullable|boolean',
            'notification_time' => 'nullable|date_format:H:i',
            'days_in_advance' => 'nullable|integer|min:1|max:30',
            'send_event_updates' => 'nullable|boolean',
            'send_club_news' => 'nullable|boolean',
        ]);

        $preferences = $user->telegramPreference()->firstOrCreate(
            ['user_id' => $user->id],
            ['notifications_enabled' => true]
        );

        $preferences->update(array_filter($validated));

        // Notify user in Telegram
        if ($user->telegram_chat_id) {
            $this->telegramService->sendMessage(
                $user->telegram_chat_id,
                "✅ Your preferences have been updated!"
            );
        }

        return response()->json([
            'message' => 'Preferences updated successfully',
            'preferences' => $preferences,
        ]);
    }

    /**
     * Unlink Telegram account
     * DELETE /api/telegram/unlink
     */
    public function unlinkAccount(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($user->telegram_chat_id) {
            $this->telegramService->sendMessage(
                $user->telegram_chat_id,
                "Your account has been unlinked from our service.\n\n" .
                "You won't receive any more notifications."
            );
        }

        $user->update([
            'telegram_chat_id' => null,
            'telegram_connected' => false,
        ]);

        $user->telegramPreference()->delete();

        return response()->json(['message' => 'Telegram account unlinked successfully']);
    }

    /**
     * Get this week's events
     * GET /api/telegram/events/thisweek
     */
    public function getThisWeekEvents(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $now = \Carbon\Carbon::now();
        $weekEnd = \Carbon\Carbon::now()->addDays(7);

        $query = \App\Models\Event::whereBetween('date', [$now->toDateString(), $weekEnd->toDateString()])
            ->where('status', 'upcoming')
            ->orderBy('date', 'asc')
            ->with('club');

        // Filter by user preferences
        if ($user->telegramPreference && $user->telegramPreference->category_preferences) {
            $query->whereIn('category', $user->telegramPreference->category_preferences);
        }

        $events = $query->get();

        return response()->json([
            'count' => $events->count(),
            'events' => $events->map(function ($event) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'date' => $event->date,
                    'start_time' => $event->start_time,
                    'location' => $event->location,
                    'category' => $event->category,
                    'club' => $event->club->name ?? 'Unknown',
                    'attendees' => $event->expected_attendees,
                ];
            }),
        ]);
    }
}
