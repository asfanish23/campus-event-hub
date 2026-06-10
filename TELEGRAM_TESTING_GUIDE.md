# 🧪 Telegram Bot Integration - Testing Guide

## Quick Start Testing

### Test 1: Verify Installation (2 minutes)
```bash
cd c:\laragon\www\CampusEventHub

# Check if package is installed
composer show | grep telegram
# Should show: irazasyed/telegram-bot-sdk

# Check if migrations ran
php artisan tinker
→ DB::table('users')->columns();
# Should include: telegram_chat_id, telegram_connected
→ DB::table('telegram_user_preferences')->columns();
# Should exist
→ exit
```

**Expected Result**: ✅ Both tables exist with correct columns

---

### Test 2: Test Service Layer (3 minutes)
```bash
php artisan tinker

# Get the service
→ $service = app(\App\Services\TelegramBotService::class);

# Test bot connection
→ $botInfo = $service->getBotUsername();
→ dd($botInfo);
# Should show: ASEEMSUlTMBot

# Test sending message (use your own Telegram chat ID)
→ $result = $service->sendMessage('YOUR_TELEGRAM_CHAT_ID', 'Testing the bot!');
→ dd($result);
# Should show success response

→ exit
```

**Expected Result**: ✅ Bot responds and message is sent

---

### Test 3: Test API Routes (5 minutes)

#### A. Test Webhook Route (Public)
```bash
# This should accept POST requests
curl -X POST http://localhost/api/telegram/webhook \
  -H "Content-Type: application/json" \
  -d '{"update_id": 12345, "message": {"text": "/start"}}'

# Should return: {"ok":true}
```

**Expected Result**: ✅ Returns 200 OK with {"ok":true}

#### B. Test Authenticated Routes
First, get an auth token:
```bash
php artisan tinker
→ $user = \App\Models\User::first();
→ $token = $user->createToken('test')->plainTextToken;
→ echo $token;
→ exit
```

Then test:
```bash
# Replace TOKEN with your token
TOKEN="YOUR_TOKEN"

# Test get preferences (should fail if no telegram connection)
curl -X GET http://localhost/api/telegram/preferences \
  -H "Authorization: Bearer $TOKEN"

# Test link account
curl -X POST http://localhost/api/telegram/link \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"telegram_chat_id": "123456789"}'

# Test get preferences again
curl -X GET http://localhost/api/telegram/preferences \
  -H "Authorization: Bearer $TOKEN"

# Test update preferences
curl -X PUT http://localhost/api/telegram/preferences \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "category_preferences": ["Sports", "Tech"],
    "notification_time": "09:00"
  }'

# Test get week events
curl -X GET http://localhost/api/telegram/events/thisweek \
  -H "Authorization: Bearer $TOKEN"
```

**Expected Result**: ✅ All endpoints return 200 with JSON responses

---

### Test 4: Test Commands (3 minutes)
```bash
# Test the weekly recommendation command
php artisan telegram:send-weekly-recommendations

# Should output something like:
# Sending weekly event recommendations at 09:00...
# ✓ Sent recommendations to John Doe
# ✅ Completed! Sent: 1, Failed: 0
```

**Expected Result**: ✅ Command runs without errors

---

## Full Integration Testing

### Test 5: End-to-End Bot Test (15 minutes)

#### Step 1: Create Test User
```bash
php artisan tinker
→ $user = \App\Models\User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => bcrypt('password123'),
    'role' => 'student'
  ]);
→ echo $user->id;
→ exit
```

#### Step 2: Link Telegram (Via API)
```bash
# Get token for test user
php artisan tinker
→ $user = \App\Models\User::find(USERID);
→ $token = $user->createToken('test')->plainTextToken;
→ echo $token;
→ exit

# Link Telegram account
curl -X POST http://localhost/api/telegram/link \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "telegram_chat_id": "YOUR_TELEGRAM_CHAT_ID",
    "telegram_username": "your_username"
  }'

# Verify in database
php artisan tinker
→ \App\Models\User::find(USERID)->telegram_chat_id;
# Should show YOUR_TELEGRAM_CHAT_ID
→ exit
```

#### Step 3: Set Preferences
```bash
curl -X PUT http://localhost/api/telegram/preferences \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "category_preferences": ["Sports", "Tech"],
    "notification_time": "09:00",
    "days_in_advance": 7,
    "notifications_enabled": true
  }'
```

#### Step 4: Test Week Events
```bash
curl -X GET http://localhost/api/telegram/events/thisweek \
  -H "Authorization: Bearer TOKEN"

# Should return events for next 7 days
```

#### Step 5: Send Notification
```bash
php artisan tinker
→ $service = app(\App\Services\TelegramBotService::class);
→ $service->sendNotification(USERID, "Test Event", "This is a test notification");
→ exit

# Check Telegram - should receive message
```

#### Step 6: Test Commands in Telegram
1. Search for `@ASEEMSUlTMBot` in Telegram
2. Type `/start` - should see welcome
3. Type `/menu` - should see button menu
4. Click "📅 This Week Events" - should show events
5. Click "⚙️ Preferences" - should show categories
6. Type `/preferences` - should show categories
7. Click a category - should toggle it

**Expected Result**: ✅ All commands work and show correct data

---

## Database Testing

### Test 6: Verify Database State

```bash
php artisan tinker

# Check users table
→ DB::table('users')->where('telegram_connected', true)->first();

# Check preferences
→ $pref = \App\Models\TelegramUserPreference::with('user')->first();
→ dd($pref);

# Check relationships
→ $user = \App\Models\User::with('telegramPreference')->find(1);
→ dd($user);

→ exit
```

**Expected Result**: ✅ Data is properly stored and related

---

## Performance Testing

### Test 7: Load Testing

```bash
# Test sending bulk messages
php artisan tinker
→ $service = app(\App\Services\TelegramBotService::class);

# Send 10 messages
→ for ($i = 0; $i < 10; $i++) {
    $service->sendMessage('YOUR_TELEGRAM_CHAT_ID', "Message $i");
    usleep(500000); // 500ms delay
  }

# Should complete without timeouts

→ exit
```

**Expected Result**: ✅ Messages send in sequence

---

## Error Handling Testing

### Test 8: Error Scenarios

#### Test Invalid Token
```bash
curl -X GET http://localhost/api/telegram/preferences \
  -H "Authorization: Bearer INVALID_TOKEN"

# Should return: 401 Unauthorized
```

#### Test Missing Parameters
```bash
curl -X POST http://localhost/api/telegram/link \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"telegram_username": "test"}'
# Missing telegram_chat_id

# Should return: 422 Validation Error
```

#### Test Unlinked User
```bash
# Create user without Telegram connection
php artisan tinker
→ $user = \App\Models\User::create([...]);
→ exit

# Try to get preferences
curl -X GET http://localhost/api/telegram/preferences \
  -H "Authorization: Bearer TOKEN"

# Should return: "linked": false
```

**Expected Result**: ✅ Proper error responses

---

## Webhook Testing

### Test 9: Webhook Simulation

Save this as `telegram_update.json`:
```json
{
  "update_id": 123456789,
  "message": {
    "message_id": 1,
    "date": 1707027600,
    "chat": {
      "id": "YOUR_TELEGRAM_CHAT_ID",
      "type": "private"
    },
    "from": {
      "id": "YOUR_TELEGRAM_CHAT_ID",
      "first_name": "Test"
    },
    "text": "/start"
  }
}
```

Then test:
```bash
curl -X POST http://localhost/api/telegram/webhook \
  -H "Content-Type: application/json" \
  -d @telegram_update.json

# Should return: {"ok":true}

# Check logs for webhook processing
tail -f storage/logs/laravel.log | grep Telegram
```

**Expected Result**: ✅ Webhook processes update and returns 200

---

## Logging Verification

### Test 10: Check Logs

```bash
# Monitor logs in real-time
tail -f storage/logs/laravel.log

# Then send a message to the bot in Telegram
# You should see log entries like:
# [2026-02-04 XX:XX:XX] local.INFO: Telegram webhook received [...]
# [2026-02-04 XX:XX:XX] local.INFO: Telegram sendMessage [...]
```

**Expected Result**: ✅ All actions are logged

---

## Postman Collection

For easy testing, import this Postman collection:

```json
{
  "info": {
    "name": "Telegram Bot API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Link Account",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}"
          }
        ],
        "url": "{{base_url}}/api/telegram/link",
        "body": {
          "mode": "raw",
          "raw": "{\"telegram_chat_id\": \"123456789\"}"
        }
      }
    },
    {
      "name": "Get Preferences",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}"
          }
        ],
        "url": "{{base_url}}/api/telegram/preferences"
      }
    },
    {
      "name": "Update Preferences",
      "request": {
        "method": "PUT",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}"
          }
        ],
        "url": "{{base_url}}/api/telegram/preferences",
        "body": {
          "mode": "raw",
          "raw": "{\"category_preferences\": [\"Sports\"], \"notification_time\": \"09:00\"}"
        }
      }
    },
    {
      "name": "Get This Week Events",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}"
          }
        ],
        "url": "{{base_url}}/api/telegram/events/thisweek"
      }
    },
    {
      "name": "Webhook Test",
      "request": {
        "method": "POST",
        "url": "{{base_url}}/api/telegram/webhook",
        "body": {
          "mode": "raw",
          "raw": "{\"update_id\": 1, \"message\": {\"text\": \"/start\"}}"
        }
      }
    }
  ],
  "variable": [
    {
      "key": "base_url",
      "value": "http://localhost"
    },
    {
      "key": "token",
      "value": "YOUR_TOKEN_HERE"
    }
  ]
}
```

---

## Test Checklist

Run through these in order:

- [ ] Test 1: Verify Installation (2 min)
- [ ] Test 2: Test Service Layer (3 min)
- [ ] Test 3: Test API Routes (5 min)
- [ ] Test 4: Test Commands (3 min)
- [ ] Test 5: End-to-End Bot Test (15 min)
- [ ] Test 6: Database Testing (5 min)
- [ ] Test 7: Performance Testing (5 min)
- [ ] Test 8: Error Handling (5 min)
- [ ] Test 9: Webhook Testing (5 min)
- [ ] Test 10: Logging Verification (5 min)

**Total Time**: ~60 minutes for complete testing

---

## Success Criteria

✅ **All tests pass** if:
1. Service sends messages successfully
2. API endpoints return correct responses
3. Commands work in Telegram
4. Database stores preferences correctly
5. Webhook receives and processes updates
6. No errors in logs
7. All error scenarios handled gracefully

---

## Troubleshooting Tests

If a test fails:

### Test 1-2 Failing: Installation Issue
```bash
# Reinstall package
composer require irazasyed/telegram-bot-sdk
php artisan migrate
```

### Test 3-4 Failing: Route Issue
```bash
# Clear routes
php artisan route:clear
php artisan route:list | grep telegram
```

### Test 5 Failing: Bot Connection Issue
```bash
# Check token in .env
cat .env | grep TELEGRAM
# Verify token is correct
```

### Test 6 Failing: Database Issue
```bash
# Reset database
php artisan migrate:fresh
# Re-run migrations
php artisan migrate
```

### Test 9 Failing: Webhook Issue
```bash
# Check logs for errors
tail -f storage/logs/laravel.log

# Verify webhook URL will be accessible
# (must be HTTPS for production)
```

---

## Next: Production Testing

Once all tests pass:

1. **Set real webhook**: Use production domain
2. **Enable scheduling**: Add cron job
3. **Load test**: Simulate multiple users
4. **Monitor logs**: Check for issues
5. **User acceptance**: Test with real users

---

**Test Suite Version**: 1.0  
**Last Updated**: February 4, 2026  
**Estimated Time**: 60 minutes
