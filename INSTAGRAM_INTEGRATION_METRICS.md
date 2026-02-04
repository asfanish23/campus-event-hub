# Instagram Integration - From Plug & Play to Integrated System

## Overview

The Instagram Graph API integration has been enhanced from a simple **plug & play posting system** to a fully **integrated netcentric element** that deeply integrates Instagram data into the core platform.

## What Changed

### 1. Database Schema (Migration)
**File**: `database/migrations/2026_02_02_000000_add_instagram_metrics_to_events_table.php`

Added columns to the `events` table to store Instagram engagement data:
- `instagram_media_id` - The Instagram post ID (unique identifier)
- `instagram_posted_at` - Timestamp when event was posted to Instagram
- `instagram_last_synced_at` - When metrics were last fetched from Instagram
- `instagram_likes_count` - Total likes on the Instagram post
- `instagram_comments_count` - Total comments on the Instagram post
- `instagram_reach` - Number of people who saw the post
- `instagram_impressions` - Total impressions
- `instagram_engagement_rate` - Calculated engagement percentage

### 2. Event Model Enhancement
**File**: `app/Models/Event.php`

Added methods:
- `isPostedToInstagram()` - Check if event has been posted to Instagram
- `getInstagramEngagement()` - Get total engagement (likes + comments)
- `needsInstagramSync()` - Check if metrics need updating (not synced in last hour)

### 3. Instagram Sync Service (NEW)
**File**: `app/Services/InstagramSyncService.php`

Handles background synchronization of Instagram metrics:
- `syncAllEventMetrics()` - Sync metrics for all events that need updating
- `syncEventMetrics($event)` - Sync metrics for a specific event
- `getSyncStatus()` - Get status of Instagram metrics across all events
- Automatically triggered notifications when milestones are reached

### 4. Instagram Service Enhancement
**File**: `app/Services/InstagramService.php`

Added new method:
- `getMediaInsights($mediaId, $accessToken)` - Fetch engagement metrics from Instagram Graph API
  - Retrieves: likes, comments, reach, impressions
  - Calculates engagement rate automatically

### 5. Notification System (NEW)
**File**: `app/Services/InstagramNotificationService.php`

Creates notifications for Instagram activity with milestone tracking:
- `createPostNotification($event)` - When event is posted to Instagram
- `createSyncNotification($event)` - When metrics are updated
- `checkEngagementMilestones($event)` - Tracks: 10, 25, 50, 100, 250, 500, 1000+ interactions
- `checkReachMilestones($event)` - Tracks: 50, 100, 250, 500, 1000, 2500, 5000+ people reached
- `getUnreadNotifications($clubId)` - Get unread activity notifications
- `getRecentNotifications($clubId)` - Get activity history

**File**: `app/Models/InstagramActivityNotification.php`

Stores notification data:
- Activity type (post_created, engagement_milestone, reach_milestone, sync_complete)
- Milestone values and labels
- Read/unread status with timestamps
- Related event and club

### 6. Event Controller Update
**File**: `app/Http/Controllers/Web/EventController.php`

When an event is created and posted to Instagram:
- Saves the Instagram media ID to the event
- Records the posting timestamp
- Sets initial sync timestamp
- Creates a "post created" notification

### 7. Dashboard Integration
**File**: `resources/views/dashboard/index.blade.php`
**File**: `app/Http/Controllers/Web/DashboardController.php`

Club dashboard now displays:
- Recent Instagram activity notifications
- Shows unread activity count
- Links to detailed Instagram analytics page
- Live updates of Instagram engagement

### 8. Event Detail Page Enhancement
**File**: `resources/views/event/show.blade.php`

Events now display Instagram engagement widget:
- Heart icon with like count
- Comment icon with comment count
- Eye icon with reach number
- Bar chart-like impressions display
- Engagement rate percentage
- Total interaction counter
- Last synced timestamp
- Only shown if event was posted to Instagram

### 9. Sync Command (NEW)
**File**: `app/Console/Commands/SyncInstagramMetrics.php`

Artisan command for manual synchronization:
```bash
php artisan instagram:sync-metrics
```

Features:
- Syncs all events that haven't been synced in the last hour
- Displays summary of successful/failed syncs
- Shows current sync status
- Lists top engaged events

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    Event Creation                           │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
        ┌──────────────────────────────────────┐
        │  Post to Instagram (Club Account)    │
        │  via InstagramService                │
        └────────┬─────────────────────────────┘
                 │
                 ├─► Save media_id to event
                 ├─► Set instagram_posted_at
                 └─► Create "post created" notification
                     
                 ▼ (Every hour via cron job or manual command)
        
        ┌──────────────────────────────────────┐
        │  Sync Instagram Metrics              │
        │  via InstagramSyncService            │
        └────────┬─────────────────────────────┘
                 │
                 ├─► Fetch insights from Graph API
                 ├─► Update database with metrics
                 ├─► Check engagement milestones
                 ├─► Check reach milestones
                 ├─► Create milestone notifications
                 └─► Create sync notifications
                     
                 ▼

        ┌──────────────────────────────────────┐
        │  Display on Platform                 │
        │  - Event detail page                 │
        │  - Dashboard notifications           │
        │  - Event list cards                  │
        └──────────────────────────────────────┘
```

## Data Flow Example

### Event Creation with Instagram Posting
```
1. Club admin creates event with image
   ↓
2. Event saved to database
   ↓
3. Image posted to club's Instagram account
   ↓
4. Instagram returns media_id
   ↓
5. media_id saved to events.instagram_media_id
   ↓
6. "Post Created" notification generated
   ↓
7. Admin sees notification on dashboard
```

### Metrics Synchronization
```
1. Sync command runs (hourly via scheduler)
   ↓
2. Find all events with media_id
   ↓
3. For each event:
   - Fetch insights from Instagram API
   - Compare with previous metrics
   - Update database
   - Check for milestone achievements
   - Create notifications if milestones reached
   ↓
4. Dashboard automatically shows updated metrics
   ↓
5. Event detail page displays latest engagement
```

### Notification Examples
```
Post Created:
  ✨ Your event "Tech Talk 2025" was successfully posted to Instagram!

Engagement Milestone:
  🎉 Your post reached 50 total interactions (likes + comments)!

Reach Milestone:
  📈 Your post has reached 250 people on Instagram!

Metrics Updated:
  ✅ Instagram metrics for "Tech Talk 2025" have been updated!
  Engagement: 45 | Reach: 280 people
```

## Key Differences: Before vs After

### Before (Plug & Play)
- ❌ Post event to Instagram and forget
- ❌ No metrics stored in database
- ❌ No way to track engagement over time
- ❌ Instagram data isolated from platform
- ❌ No notifications about Instagram activity
- ❌ Can easily disable without impact
- ❌ Optional feature

### After (Integrated)
- ✅ Event metrics are part of event data model
- ✅ All Instagram metrics stored in database
- ✅ Historical tracking of engagement
- ✅ Instagram data displayed throughout platform
- ✅ Milestone notifications integrated into notification system
- ✅ Dashboard shows Instagram activity
- ✅ Event detail pages display engagement stats
- ✅ Scheduled sync service keeps data current
- ✅ Admin visibility into Instagram performance
- ✅ Still optional but deeply integrated

## Usage

### For Club Admins

1. **See Instagram Activity**
   - Go to Dashboard
   - View recent Instagram notifications
   - See which events are getting engagement

2. **Track Event Performance**
   - Click on event from list
   - View Instagram engagement stats
   - Track reach and impressions over time

3. **Manual Sync (if needed)**
   - Run: `php artisan instagram:sync-metrics`
   - View current sync status
   - See top-performing events

### For Developers

#### Creating a Notification
```php
$notificationService = app(InstagramNotificationService::class);

// Post created
$notificationService->createPostNotification($event);

// Sync complete
$notificationService->createSyncNotification($event);

// Check milestones automatically
$notificationService->checkEngagementMilestones($event, $previousMetrics);
$notificationService->checkReachMilestones($event, $previousMetrics);
```

#### Syncing Metrics Manually
```php
$syncService = app(InstagramSyncService::class);

// Sync all events
$results = $syncService->syncAllEventMetrics();

// Sync specific event
$syncResult = $syncService->syncEventMetrics($event);

// Get status
$status = $syncService->getSyncStatus();
```

#### Getting Notifications
```php
$notificationService = app(InstagramNotificationService::class);

// Get recent notifications
$notifications = $notificationService->getRecentNotifications($clubId, 20);

// Get unread only
$unreadNotifications = $notificationService->getUnreadNotifications($clubId);

// Count unread
$count = $notificationService->getUnreadCount($clubId);
```

## Scheduled Task (Optional)

To automatically sync metrics every hour, add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('instagram:sync-metrics')
        ->hourly()
        ->withoutOverlapping();
}
```

## API Calls

The system uses Instagram Graph API v18.0 for:
1. **Posting** (via existing InstagramService)
   - POST /business_id/media (create container)
   - POST /business_id/media_publish (publish)

2. **Fetching Insights** (new)
   - GET /media_id/insights?fields=likes_count,comments_count,reach,impressions

## Error Handling

- Failed syncs are logged but don't break the system
- Invalid tokens are detected and reported
- Milestone notifications prevent duplicates
- All operations have fallbacks

## Performance

- Sync only runs on events that need updating (not synced in 1+ hour)
- Database indexes on instagram_media_id for fast lookups
- Milestone checking uses efficient array comparisons
- Notifications only created when milestones are newly achieved

## Conclusion

The Instagram integration is now a **true integrated netcentric element** that:
- Syncs data back into the platform
- Displays metrics throughout the interface
- Creates notifications for milestone achievements
- Allows admins to track Instagram performance
- Maintains historical engagement data
- Is scheduled to run automatically
- Is deeply embedded in the event system

This makes it a **critical business intelligence tool** while remaining as an optional feature clubs can use.
