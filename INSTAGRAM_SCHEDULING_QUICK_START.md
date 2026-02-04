# Instagram Auto-Post & Scheduling - Quick Start Guide

## For Club Admins - How to Use

### Creating an Event with Instagram Auto-Post

1. **Go to Event Creation**
   - Navigate to Dashboard → Event List → Create Event

2. **Fill Event Details**
   - Event name, date, time, location, description
   - Upload event cover image (required for Instagram)

3. **Enable Instagram Posting**
   - Scroll to "📱 Instagram Auto-Posting" section
   - ✅ Check "Auto-post to Instagram when event is created"

4. **Choose Posting Timing**

   **Option A: Post Immediately**
   - Select "Post Immediately" radio button
   - Event will post to Instagram right away after creation
   - Takes 1-2 seconds to process

   **Option B: Schedule for Later**
   - Select "Schedule Post for Later" radio button
   - Pick date and time (minimum 5 minutes in future)
   - Event will be saved and posted at scheduled time
   - Admin must run `php artisan instagram:process-scheduled-posts` command at/after that time

5. **Create Event**
   - Click "Create Event" button
   - ✅ Event is created and Instagram post is scheduled/sent

### Viewing Posted Events

- Go to Instagram page to see all posted events
- View engagement metrics (likes, comments, reach, impressions)
- Track performance of each event

---

## For System Admins - Setup & Configuration

### Installation & Migration

```bash
# Run database migrations
php artisan migrate

# Verify tables updated
php artisan tinker
> DB::table('events')->getColumns();
```

### Processing Scheduled Posts

#### Manual Processing (On-Demand)
```bash
# Process scheduled posts
php artisan instagram:process-scheduled-posts

# With verbose output
php artisan instagram:process-scheduled-posts --verbose
```

#### Automatic Processing (Recommended)

Add to server crontab for automatic processing:

```bash
# Edit crontab
crontab -e

# Add this line to process every 5 minutes:
*/5 * * * * cd /path/to/CampusEventHub && php artisan instagram:process-scheduled-posts >> /var/log/scheduled-posts.log 2>&1

# Or every hour:
0 * * * * cd /path/to/CampusEventHub && php artisan instagram:process-scheduled-posts >> /var/log/scheduled-posts.log 2>&1
```

#### Using Task Scheduler (Alternative)

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('instagram:process-scheduled-posts')
        ->everyFiveMinutes()
        ->runInBackground();
}
```

---

## Database Schema

```sql
-- New columns added to events table
instagram_auto_post BOOLEAN DEFAULT FALSE
instagram_scheduled_at TIMESTAMP NULL
instagram_scheduled_posted BOOLEAN DEFAULT FALSE

-- Index for performance
INDEX (instagram_auto_post, instagram_scheduled_at)
```

---

## Key Features

✅ **Immediate Posting** - Post event to Instagram instantly
✅ **Scheduled Posting** - Schedule event for specific date/time
✅ **Automatic Processing** - Run command at scheduled time
✅ **Error Handling** - Comprehensive error logging
✅ **Image Validation** - Ensures image exists before posting
✅ **Token Validation** - Checks Instagram token validity
✅ **Metrics Tracking** - Automatically syncs engagement metrics
✅ **Notification System** - Creates notifications for posts

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Event won't post immediately | Check if event image is uploaded |
| Scheduled post not processing | Run command: `php artisan instagram:process-scheduled-posts` |
| "Event image file not found" | Verify image uploaded to storage |
| Instagram token errors | Reconnect Instagram account in Club Profile |
| Minimum datetime error | Set time at least 5 minutes in future |

---

## Files Reference

| File | Purpose |
|------|---------|
| `app/Models/Event.php` | Model with new scheduling fields |
| `app/Http/Controllers/Web/EventController.php` | Handles form submission & posting |
| `app/Services/ScheduledInstagramPostService.php` | Processing scheduled posts |
| `app/Console/Commands/ProcessScheduledInstagramPosts.php` | Artisan command |
| `resources/views/event/create.blade.php` | Event creation form |
| `database/migrations/*scheduling*` | Database migration |

---

## Examples

### Database Query - Get Scheduled Posts Ready

```php
$readyPosts = Event::getScheduledPostsReady();
// Returns all events ready to be posted to Instagram

// Manual query equivalent:
$readyPosts = Event::where('instagram_auto_post', true)
    ->where('instagram_scheduled_posted', false)
    ->whereNotNull('instagram_scheduled_at')
    ->whereNull('instagram_media_id')
    ->where('instagram_scheduled_at', '<=', now())
    ->get();
```

### Check Event Status

```php
$event = Event::find($eventId);

// Is it ready for immediate posting?
$event->isReadyForInstagramAutoPost(); // true/false

// Is it ready for scheduled posting?
$event->isReadyForScheduledInstagramPost(); // true/false

// Has it been posted?
$event->isPostedToInstagram(); // true/false
```

### Process Posts Programmatically

```php
use App\Services\ScheduledInstagramPostService;

$service = app(ScheduledInstagramPostService::class);

// Process all ready posts
$results = $service->processScheduledPosts();
// Returns: ['success' => 2, 'failed' => 1, 'skipped' => 0, 'errors' => [...]]

// Check specific event status
$status = $service->getScheduledPostStatus($event);
// Returns: ['event_id' => 1, 'scheduled_at' => ..., 'is_posted' => false, ...]
```

---

## Support

For issues or questions:
1. Check logs in `storage/logs/`
2. Run command with `--verbose` flag
3. Review `INSTAGRAM_AUTO_POST_SCHEDULING_GUIDE.md` for details
4. Check event has image and Instagram account is connected
