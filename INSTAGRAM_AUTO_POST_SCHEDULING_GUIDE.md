# Instagram Auto-Post & Scheduling Feature Implementation

## Overview
Club admins can now enable automatic Instagram posting when creating events, with options to post immediately or schedule for a specific date and time.

## Features Added

### 1. Database Schema
Added three new fields to the `events` table via migration `2026_02_05_000000_add_instagram_scheduling_to_events_table.php`:

- **`instagram_auto_post`** (boolean, default: false)
  - Enables/disables automatic Instagram posting
  
- **`instagram_scheduled_at`** (timestamp, nullable)
  - Stores the scheduled time for posting if delayed
  
- **`instagram_scheduled_posted`** (boolean, default: false)
  - Tracks whether a scheduled post has been successfully processed
  
- **Index**: `instagram_auto_post`, `instagram_scheduled_at` for efficient querying

### 2. Event Model Updates (`app/Models/Event.php`)

#### New Fillable Properties:
```php
'instagram_auto_post',
'instagram_scheduled_at',
'instagram_scheduled_posted',
```

#### New Casts:
```php
'instagram_auto_post' => 'boolean',
'instagram_scheduled_at' => 'datetime',
'instagram_scheduled_posted' => 'boolean',
```

#### New Helper Methods:
- `isReadyForInstagramAutoPost()` - Checks if event is ready for immediate posting
- `isReadyForScheduledInstagramPost()` - Checks if event is ready for scheduled posting
- `getScheduledPostsReady()` - Static method to get all events ready for scheduled posting

### 3. Form UI Updates (`resources/views/event/create.blade.php`)

New section added with:

**Checkbox:** "Auto-post to Instagram when event is created"
- Enables/disables the Instagram posting options

**Radio Buttons (conditional):**
1. **Post Immediately**
   - Posts to Instagram right away when creating the event
   
2. **Schedule Post for Later**
   - Shows a datetime picker
   - Allows selecting a specific date and time for posting

**Features:**
- Minimum datetime validation (at least 5 minutes in future)
- Visual feedback with styling
- Help text explaining functionality

### 4. Controller Updates (`app/Http/Controllers/Web/EventController.php`)

#### Updated Validation:
```php
'instagram_auto_post' => 'nullable|boolean',
'instagram_post_timing' => 'nullable|in:immediate,scheduled',
'instagram_scheduled_at' => 'nullable|date_format:Y-m-d\TH:i|after:now',
```

#### Processing Logic:
- If `instagram_auto_post` is true:
  - If scheduled time is in future: Save event without posting
  - If immediate or no scheduled time: Post immediately
  
#### New Helper Method:
`postEventToInstagram()` - Handles the actual Instagram posting with proper error handling

### 5. Service Class: `ScheduledInstagramPostService`

Location: `app/Services/ScheduledInstagramPostService.php`

**Key Methods:**
- `processScheduledPosts()` - Process all ready scheduled posts
- `postScheduledEvent()` - Post a single scheduled event to Instagram
- `getScheduledPostStatus()` - Get status information about a scheduled post

**Features:**
- Comprehensive error handling
- Logging for all operations
- Returns detailed results with success/failure counts
- Validates event requirements before posting

### 6. Artisan Command: `ProcessScheduledInstagramPosts`

Location: `app/Console/Commands/ProcessScheduledInstagramPosts.php`

**Command Signature:**
```bash
php artisan instagram:process-scheduled-posts {--verbose}
```

**Usage:**
```bash
# Run scheduled posts processing
php artisan instagram:process-scheduled-posts

# Run with verbose output
php artisan instagram:process-scheduled-posts --verbose
```

**Features:**
- Displays formatted results
- Shows error details in verbose mode
- Proper exit codes for automation
- Emoji indicators for better readability

## How It Works

### Immediate Posting Flow:
1. Club admin creates event with event image
2. Checks "Auto-post to Instagram"
3. Selects "Post Immediately"
4. Event is created and posted to Instagram immediately
5. Instagram media ID and post timestamp are saved

### Scheduled Posting Flow:
1. Club admin creates event with event image
2. Checks "Auto-post to Instagram"
3. Selects "Schedule Post for Later"
4. Selects desired date and time (minimum 5 minutes in future)
5. Event is created with scheduling information saved
6. On scheduled time, admin runs: `php artisan instagram:process-scheduled-posts`
7. Command processes and posts the event to Instagram

### Cron Setup (Optional - For Automatic Processing)

To process scheduled posts automatically, add to your server's crontab:

```bash
# Process scheduled Instagram posts every 5 minutes
*/5 * * * * cd /path/to/CampusEventHub && php artisan instagram:process-scheduled-posts
```

## Database Migration

Run migration to apply changes:
```bash
php artisan migrate
```

## Validation & Error Handling

**Form Validation:**
- Event image is required for Instagram posting
- Scheduled datetime must be in the future
- Proper datetime format validation

**Processing Errors Handled:**
- Missing event image
- Club not found
- Instagram account not configured
- Token validity
- Image file not found
- Instagram API failures

All errors are logged with detailed information for debugging.

## API Endpoints Used

The feature leverages existing Instagram services:
- `ClubInstagramService::postEventToClubInstagram()` - Posts to club Instagram
- `InstagramNotificationService::createPostNotification()` - Creates notification

## Environment Requirements

- Event must have an event image (main cover image)
- Club must have Instagram account connected
- Instagram access token must be valid
- Server must have storage access

## Tracking & Analytics

After posting, the following metrics are tracked:
- `instagram_media_id` - The unique Instagram media ID
- `instagram_posted_at` - Timestamp when posted
- `instagram_last_synced_at` - Last sync with Instagram metrics
- `instagram_likes_count` - Number of likes
- `instagram_comments_count` - Number of comments
- `instagram_reach` - Estimated reach
- `instagram_impressions` - Number of impressions
- `instagram_engagement_rate` - Engagement percentage

## Testing

### Test Immediate Posting:
1. Create event with image
2. Check "Auto-post to Instagram"
3. Select "Post Immediately"
4. Verify post appears in club Instagram

### Test Scheduled Posting:
1. Create event with image
2. Check "Auto-post to Instagram"
3. Select "Schedule Post for Later"
4. Set time to 1 minute in future
5. Wait or run: `php artisan instagram:process-scheduled-posts`
6. Verify post appears in club Instagram

## Future Enhancements

- Dashboard display of scheduled posts
- Edit functionality for scheduled posts
- Cancel/reschedule options
- Bulk scheduling
- Post preview before sending
- A/B testing different captions/times
- Analytics dashboard integration

## Files Modified/Created

### Created:
- Migration: `database/migrations/2026_02_05_000000_add_instagram_scheduling_to_events_table.php`
- Service: `app/Services/ScheduledInstagramPostService.php`
- Command: `app/Console/Commands/ProcessScheduledInstagramPosts.php`

### Modified:
- Model: `app/Models/Event.php`
- Controller: `app/Http/Controllers/Web/EventController.php`
- View: `resources/views/event/create.blade.php`

## Troubleshooting

**Issue: Event not posting immediately**
- Check if event has image uploaded
- Verify club has Instagram account connected
- Check if token is still valid
- Review logs for detailed error messages

**Issue: Scheduled posts not processing**
- Ensure command is running: `php artisan instagram:process-scheduled-posts`
- Check if scheduled time has passed
- Verify event has image file present
- Review logs for errors

**Issue: "Scheduled datetime must be in the future"**
- Datetime picker minimum is set to 5 minutes from now
- Select a time further in the future
- Check server time is correct
