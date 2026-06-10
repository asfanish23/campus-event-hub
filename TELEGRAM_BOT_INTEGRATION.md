# Telegram Bot Integration - Complete Guide

## Overview
Your CampusEventHub application now has full Telegram bot integration with:
- ✅ Real-time notifications
- ✅ User preference management
- ✅ Smart event recommendations
- ✅ Weekly event digests

## Bot Information
- **Bot Username**: @ASEEMSUlTMBot
- **Bot Token**: `YOUR_TELEGRAM_BOT_TOKEN`

---

## Features

### 1. 🔔 Sending Notifications
The bot can send various types of notifications to users:

#### Types of Notifications:
- **Event Updates**: When new events are added
- **Weekly Recommendations**: Based on user preferences
- **Event Reminders**: Before events start
- **Club News**: Updates from clubs the user follows

#### Implementation:
```php
use App\Services\TelegramBotService;

$telegramService = app(TelegramBotService::class);

// Send simple message
$telegramService->sendMessage($chatId, "Your message here");

// Send notification with title and message
$telegramService->sendNotification(
    userId: 1,
    title: "New Event",
    message: "Tech Workshop is coming tomorrow!"
);

// Send weekly recommendations
$telegramService->sendWeeklyRecommendations($user);
```

---

### 2. ⚙️ Managing User Preferences

Users can configure their preferences through:
- **Telegram Commands**: `/preferences`
- **Interactive Buttons**: Click buttons in Telegram
- **API Endpoints**: Programmatic management

#### Preference Options:
1. **Event Categories**: Select which event types to follow
2. **Notification Time**: Set preferred notification time (08:00 - 21:00)
3. **Days in Advance**: Show events for next N days (1-30)
4. **Notification Toggles**: Enable/disable notifications
5. **Content Types**: Event updates and club news

#### Database Schema:
```
telegram_user_preferences:
- user_id (FK)
- category_preferences (JSON array)
- notifications_enabled (boolean)
- notification_time (HH:MM format)
- days_in_advance (integer)
- send_event_updates (boolean)
- send_club_news (boolean)
```

---

### 3. 📅 Event Recommendations for THIS WEEK

The bot provides smart recommendations for the upcoming 7 days:

#### Features:
- ✅ Personalized based on user preferences
- ✅ Filtered by selected event categories
- ✅ Shows event details (date, time, location, attendance)
- ✅ Automatically scheduled weekly delivery
- ✅ Manual access via `/thisweek` command

#### Weekly Recommendation Command:
```bash
php artisan telegram:send-weekly-recommendations --time=09:00
```

Schedule this command in your server's cron job:
```
# Send recommendations every Monday at 9:00 AM
0 9 * * 1 cd /path/to/app && php artisan telegram:send-weekly-recommendations
```

---

## API Endpoints

All API endpoints require authentication (Bearer token) except the webhook.

### Public Endpoints

#### **POST /api/telegram/webhook**
Receives updates from Telegram. This must be configured with Telegram's servers.

```bash
curl -X POST https://yourapp.com/api/telegram/webhook \
  -H "Content-Type: application/json" \
  -d @telegram_update.json
```

### Authenticated Endpoints (require `auth:sanctum` middleware)

#### **POST /api/telegram/link**
Link a user's account to Telegram.

```bash
curl -X POST https://yourapp.com/api/telegram/link \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d {
    "telegram_chat_id": "123456789",
    "telegram_username": "username"
  }
```

**Response**:
```json
{
  "message": "Telegram account linked successfully",
  "user": {...}
}
```

#### **DELETE /api/telegram/unlink**
Unlink Telegram account from user profile.

```bash
curl -X DELETE https://yourapp.com/api/telegram/unlink \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### **GET /api/telegram/preferences**
Get user's current Telegram preferences.

```bash
curl -X GET https://yourapp.com/api/telegram/preferences \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Response**:
```json
{
  "linked": true,
  "preferences": {
    "id": 1,
    "user_id": 1,
    "category_preferences": ["Sports", "Tech"],
    "notifications_enabled": true,
    "notification_time": "09:00",
    "days_in_advance": 7,
    "send_event_updates": true,
    "send_club_news": false
  }
}
```

#### **PUT /api/telegram/preferences**
Update user's Telegram preferences.

```bash
curl -X PUT https://yourapp.com/api/telegram/preferences \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d {
    "category_preferences": ["Sports", "Music", "Tech"],
    "notification_time": "14:00",
    "notifications_enabled": true,
    "days_in_advance": 10
  }
```

#### **GET /api/telegram/events/thisweek**
Get events for this week based on user preferences.

```bash
curl -X GET https://yourapp.com/api/telegram/events/thisweek \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Response**:
```json
{
  "count": 3,
  "events": [
    {
      "id": 1,
      "name": "Tech Workshop",
      "date": "2026-02-10",
      "start_time": "15:30:00",
      "location": "Lab 101",
      "category": "Tech",
      "club": "Tech Club",
      "attendees": 50
    }
  ]
}
```

---

## Telegram Bot Commands

Users can use these commands directly in Telegram:

| Command | Description | Response |
|---------|-------------|----------|
| `/start` | Initialize bot | Welcome message |
| `/menu` | Show main menu | Interactive button menu |
| `/thisweek` | View this week's events | Formatted event list |
| `/preferences` | Configure preferences | Category selection buttons |
| `/subscribe` | Enable notifications | Confirmation message |
| `/unsubscribe` | Disable notifications | Confirmation message |
| `/help` | Show help | Command list and usage |

---

## User Flow

### 1. **First-Time Setup**
```
User opens Telegram bot
    ↓
Bot shows welcome message
    ↓
User logs into website
    ↓
User goes to Settings → Connect Telegram
    ↓
User enters Telegram chat ID
    ↓
Bot sends confirmation message
    ↓
User runs /preferences to set interests
    ↓
User receives personalized recommendations
```

### 2. **Using the Bot**
```
User types /menu
    ↓
Bot shows buttons:
  - 📅 This Week Events
  - ⚙️ Preferences
  - 🔔 Notifications
  - ❓ Help
    ↓
User clicks option
    ↓
Bot provides requested information
```

### 3. **Receiving Notifications**
```
Scheduled task runs at user's preference time
    ↓
Bot queries events for next N days
    ↓
Filters by user's category preferences
    ↓
Sends formatted event recommendations
    ↓
User can interact with events
```

---

## Installation & Setup

### Already Completed:
✅ Installed `irazasyed/telegram-bot-sdk`
✅ Created migrations and models
✅ Created TelegramBotService
✅ Created API controller
✅ Added API routes
✅ Configured environment variables

### Next Steps:

#### 1. **Set Webhook with Telegram**
Run this command to register your webhook:

```bash
php artisan tinker
```

Then in tinker:
```php
$service = app(App\Services\TelegramBotService::class);
$response = \Illuminate\Support\Facades\Http::post(
    'https://api.telegram.org/bot8298576733:AAHv-8cuYZ21no92uskc24NvQ6ON_yaNNFs/setWebhook',
    ['url' => 'https://yourapp.com/api/telegram/webhook']
);
dd($response->json());
```

**Important**: Replace `https://yourapp.com` with your actual domain (must be HTTPS).

#### 2. **Enable Scheduled Commands**
Add to your server's crontab to send weekly recommendations:

```bash
# Edit crontab
crontab -e

# Add this line (runs Monday at 9:00 AM)
0 9 * * 1 cd /var/www/campuseventhub && php artisan telegram:send-weekly-recommendations
```

#### 3. **Update Frontend (Optional)**
Add a section in user settings to link Telegram:

```html
<div class="telegram-section">
  <h3>📱 Telegram Integration</h3>
  <button id="link-telegram">Link Telegram Account</button>
</div>
```

JavaScript:
```javascript
document.getElementById('link-telegram').addEventListener('click', async () => {
  const chatId = prompt('Enter your Telegram Chat ID:');
  if (chatId) {
    const response = await fetch('/api/telegram/link', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ telegram_chat_id: chatId })
    });
    alert(await response.json());
  }
});
```

---

## Getting Telegram Chat ID

Users need their Telegram Chat ID to link their account. They can get it by:

1. **Start the bot**: Search for `@ASEEMSUlTMBot` in Telegram
2. **Use bot forwarding**: Send `/start` to the bot
3. **Get ID from message**: Run this command in terminal:

```bash
# Using direct API
curl https://api.telegram.org/bot8298576733:AAHv-8cuYZ21no92uskc24NvQ6ON_yaNNFs/getUpdates
```

Look for `"id"` in the response under `message.chat.id`.

---

## Troubleshooting

### Bot not responding to commands
1. Check webhook is set correctly: `https://yourapp.com/api/telegram/webhook`
2. Verify Telegram API key in `.env`
3. Check Laravel logs: `storage/logs/laravel.log`

### Notifications not sending
1. Verify user has `telegram_connected = true`
2. Check `telegramPreference.notifications_enabled = true`
3. Run command manually: `php artisan telegram:send-weekly-recommendations`
4. Check for errors in logs

### Webhook not receiving updates
1. Ensure domain is HTTPS (required by Telegram)
2. Check firewall allows port 443
3. Verify webhook URL is publicly accessible
4. Run: `php artisan log:tail` to see incoming requests

---

## Database Models

### User Model (Enhanced)
```php
// New fields
- telegram_chat_id (string, nullable, unique)
- telegram_connected (boolean)

// New relationship
public function telegramPreference()
```

### TelegramUserPreference Model
```php
- id
- user_id (FK)
- category_preferences (JSON)
- notifications_enabled (boolean)
- notification_time (string: HH:MM)
- days_in_advance (integer)
- send_event_updates (boolean)
- send_club_news (boolean)
```

---

## Example Workflows

### Example 1: Send Event Update to User
```php
$user = User::find(1);
$telegramService = app(TelegramBotService::class);

$telegramService->sendNotification(
    userId: $user->id,
    title: "New Event Posted!",
    message: "Tech Workshop at 3 PM tomorrow in Lab 101"
);
```

### Example 2: Send Bulk Recommendations
```php
use App\Models\User;
use App\Services\TelegramBotService;

$service = app(TelegramBotService::class);

User::where('telegram_connected', true)
    ->whereHas('telegramPreference', fn($q) => $q->where('notifications_enabled', true))
    ->each(fn($user) => $service->sendWeeklyRecommendations($user));
```

### Example 3: Get User's Week Events via API
```php
// Frontend code
const response = await fetch('/api/telegram/events/thisweek', {
  headers: { 'Authorization': `Bearer ${userToken}` }
});
const events = await response.json();
console.log(events);
```

---

## Security Considerations

1. **Webhook Validation**: Always validate webhook requests from Telegram
2. **Token Management**: Keep `TELEGRAM_BOT_TOKEN` secure in `.env`
3. **Rate Limiting**: Telegram limits to 30 messages/second per user
4. **User Verification**: Verify user authentication before linking

---

## Support & Monitoring

### Check Bot Status
```bash
# In Laravel Tinker
$service = app(\App\Services\TelegramBotService::class);
$service->getBotUsername();
```

### View Recent Logs
```bash
tail -f storage/logs/laravel.log | grep Telegram
```

### Test Webhook
```bash
php artisan log:tail
# Then send a message to bot in Telegram
```

---

## Future Enhancements

Potential features to add:
- [ ] Event registration through Telegram
- [ ] Event reminder notifications
- [ ] User reviews/ratings through Telegram
- [ ] Admin broadcast messages
- [ ] Event search in Telegram
- [ ] Calendar view in Telegram mini-app
- [ ] Payment integration for tickets

---

## Configuration Summary

| Setting | Value | Purpose |
|---------|-------|---------|
| `TELEGRAM_BOT_TOKEN` | Your bot token | Authenticate with Telegram API |
| `TELEGRAM_API_URL` | https://api.telegram.org | Telegram API endpoint |
| Webhook URL | `/api/telegram/webhook` | Receive updates from Telegram |
| Cron Command | `telegram:send-weekly-recommendations` | Schedule recommendations |

---

**Last Updated**: February 2026
**Bot Version**: 1.0
**Status**: ✅ Fully Integrated
