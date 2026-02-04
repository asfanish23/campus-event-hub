# Instagram Scheduling Feature - Flow Diagrams

## 1. Immediate Posting Flow

```
┌─────────────────────────────────────────────────────────────────┐
│ Club Admin Creates Event with Instagram Auto-Post               │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│ Form:                                                            │
│ ✓ Event details (name, date, time, location)                   │
│ ✓ Event image                                                   │
│ ✓ Additional photos                                             │
│ ✓ ☑ Auto-post to Instagram                                     │
│ ✓ ● Post Immediately (selected)                                │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│ EventController::store()                                         │
│ - Validate input                                                │
│ - Save event image                                              │
│ - Create event in database                                      │
│ - instagram_auto_post = true                                    │
│ - instagram_scheduled_at = null                                 │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│ Check: Event ready for immediate posting?                        │
│ (has image, auto_post=true, scheduled_at=null)                  │
└─────────────────────────────────────────────────────────────────┘
                            ↓
                      YES / NO
                      /       \
                    /           \
                  ✓              ✗
                  │              │
                  ↓              ↓
         ┌─────────────┐  ┌─────────────┐
         │ Post Now    │  │ Return OK   │
         └─────────────┘  └─────────────┘
              ↓
         ┌─────────────────────────┐
         │ ClubInstagramService    │
         │ postEventToClubInstagram│
         └─────────────────────────┘
              ↓
    ┌────────────────────────────┐
    │ Upload image to ImgBB      │
    │ Create caption             │
    │ Post to Instagram API      │
    └────────────────────────────┘
              ↓
         POST SUCCESS
              ↓
    ┌────────────────────────────┐
    │ Update Event:              │
    │ instagram_media_id = <id>  │
    │ instagram_posted_at = now()│
    │ instagram_scheduled_posted │
    │ = true                     │
    └────────────────────────────┘
              ↓
    ┌────────────────────────────┐
    │ Create Notification        │
    │ ✅ Posted to Instagram     │
    └────────────────────────────┘
              ↓
    ✨ EVENT POSTED TO INSTAGRAM ✨
```

## 2. Scheduled Posting Flow

```
┌─────────────────────────────────────────────────────────────────┐
│ Club Admin Creates Event with Scheduled Instagram Post           │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│ Form:                                                            │
│ ✓ Event details                                                 │
│ ✓ Event image                                                   │
│ ✓ ☑ Auto-post to Instagram                                     │
│ ✓ ○ Schedule Post for Later (selected)                          │
│ ✓ Date/Time: 2026-02-07 14:30                                   │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│ EventController::store()                                         │
│ - Validate datetime (must be future)                            │
│ - Save event image                                              │
│ - Create event in database:                                     │
│   - instagram_auto_post = true                                  │
│   - instagram_scheduled_at = 2026-02-07 14:30                   │
│   - instagram_scheduled_posted = false                          │
│   - instagram_media_id = null                                   │
└─────────────────────────────────────────────────────────────────┘
                            ↓
    ✅ EVENT CREATED & SCHEDULED
                            ↓
┌──────────────────────────────────────────┐
│ [WAITING FOR SCHEDULED TIME]             │
│                                          │
│ Current: 2026-02-07 10:00                │
│ Scheduled: 2026-02-07 14:30              │
│ Status: ⏳ Waiting...                    │
└──────────────────────────────────────────┘
                            ↓
     [2026-02-07 14:30 ARRIVES]
                            ↓
┌──────────────────────────────────────────┐
│ Admin runs Artisan command:              │
│ $ php artisan instagram:                 │
│   process-scheduled-posts                │
└──────────────────────────────────────────┘
                            ↓
┌──────────────────────────────────────────┐
│ ScheduledInstagramPostService            │
│ processScheduledPosts()                  │
│                                          │
│ 1. Get all events with:                  │
│    - instagram_auto_post = true          │
│    - instagram_scheduled_posted = false  │
│    - instagram_scheduled_at <= now()     │
│    - instagram_media_id = null           │
└──────────────────────────────────────────┘
                            ↓
┌──────────────────────────────────────────┐
│ Loop through each ready event            │
└──────────────────────────────────────────┘
                            ↓
        ┌───────────────────────────────┐
        │ For Each Event:               │
        │ postScheduledEvent()          │
        │                               │
        │ 1. Validate event             │
        │ 2. Check image exists         │
        │ 3. Get club info              │
        │ 4. Create caption             │
        │ 5. Post to Instagram          │
        └───────────────────────────────┘
                            ↓
                      POST SUCCESS
                            ↓
        ┌───────────────────────────────┐
        │ Update Event:                 │
        │ instagram_media_id = <id>     │
        │ instagram_posted_at = now()   │
        │ instagram_scheduled_posted    │
        │ = true                        │
        │ instagram_last_synced_at      │
        │ = now()                       │
        └───────────────────────────────┘
                            ↓
        ┌───────────────────────────────┐
        │ Create Notification           │
        │ ✅ Scheduled post published   │
        └───────────────────────────────┘
                            ↓
    ✨ SCHEDULED EVENT POSTED ✨
                            ↓
        ┌───────────────────────────────┐
        │ Command Output:               │
        │ ✅ Successfully posted: 1     │
        │ ❌ Failed: 0                  │
        │ ⏭️  Skipped: 0                │
        └───────────────────────────────┘
```

## 3. Database State Changes - Immediate Post

```
BEFORE:
┌─────────────────────────────────────────────┐
│ events table - NEW ROW                       │
├─────────────────────────────────────────────┤
│ id: 1                                       │
│ name: Tech Workshop                         │
│ event_image: /path/to/image.jpg             │
│ instagram_auto_post: NULL                   │
│ instagram_scheduled_at: NULL                │
│ instagram_scheduled_posted: NULL            │
│ instagram_media_id: NULL                    │
│ instagram_posted_at: NULL                   │
└─────────────────────────────────────────────┘

AFTER (Immediate Post):
┌─────────────────────────────────────────────┐
│ events table - UPDATED ROW                   │
├─────────────────────────────────────────────┤
│ id: 1                                       │
│ name: Tech Workshop                         │
│ event_image: /path/to/image.jpg             │
│ instagram_auto_post: 1 (TRUE)               │
│ instagram_scheduled_at: NULL                │
│ instagram_scheduled_posted: 1 (TRUE)        │
│ instagram_media_id: 17858843... ✨           │
│ instagram_posted_at: 2026-02-05 10:30 ✨    │
└─────────────────────────────────────────────┘
```

## 4. Database State Changes - Scheduled Post

```
BEFORE:
┌─────────────────────────────────────────────┐
│ events table - NEW ROW                       │
├─────────────────────────────────────────────┤
│ id: 2                                       │
│ name: Art Exhibition                        │
│ event_image: /path/to/image.jpg             │
│ instagram_auto_post: NULL                   │
│ instagram_scheduled_at: NULL                │
│ instagram_scheduled_posted: NULL            │
│ instagram_media_id: NULL                    │
│ instagram_posted_at: NULL                   │
└─────────────────────────────────────────────┘

IMMEDIATELY AFTER CREATION:
┌─────────────────────────────────────────────┐
│ events table - UPDATED ROW                   │
├─────────────────────────────────────────────┤
│ id: 2                                       │
│ name: Art Exhibition                        │
│ event_image: /path/to/image.jpg             │
│ instagram_auto_post: 1 (TRUE)               │
│ instagram_scheduled_at: 2026-02-07 14:30 ✨ │
│ instagram_scheduled_posted: 0 (FALSE)       │
│ instagram_media_id: NULL                    │
│ instagram_posted_at: NULL                   │
└─────────────────────────────────────────────┘

AFTER COMMAND RUN (at scheduled time):
┌─────────────────────────────────────────────┐
│ events table - UPDATED ROW                   │
├─────────────────────────────────────────────┤
│ id: 2                                       │
│ name: Art Exhibition                        │
│ event_image: /path/to/image.jpg             │
│ instagram_auto_post: 1 (TRUE)               │
│ instagram_scheduled_at: 2026-02-07 14:30    │
│ instagram_scheduled_posted: 1 (TRUE) ✨      │
│ instagram_media_id: 17858844... ✨           │
│ instagram_posted_at: 2026-02-07 14:30 ✨    │
└─────────────────────────────────────────────┘
```

## 5. Cron Job Automation

```
┌─────────────────────────────────────────────┐
│ Crontab Configuration                        │
├─────────────────────────────────────────────┤
│ */5 * * * * cd /path/to/app &&              │
│   php artisan instagram:process-scheduled.. │
│                                             │
│ Runs every 5 minutes automatically          │
└─────────────────────────────────────────────┘
         ↓ every 5 minutes ↓
    ┌──────────────────────────┐
    │ Command Execution        │
    │ - Check for ready posts  │
    │ - Post to Instagram      │
    │ - Update database        │
    │ - Log results            │
    └──────────────────────────┘
         ↓ result ↓
    ┌──────────────────────────┐
    │ Log Output:              │
    │ ✅ Posted: 2             │
    │ ❌ Failed: 0             │
    │ ⏭️  Skipped: 1            │
    └──────────────────────────┘
```

## 6. User Interface Flow

```
┌─────────────────────────────────────────────────────────────────┐
│ EVENT CREATION FORM                                              │
└─────────────────────────────────────────────────────────────────┘

Event Information (filled)
├─ Event Name
├─ Category
├─ Date & Time
└─ Location

Event Image (uploaded)
└─ /storage/event-images/image.jpg

Additional Photos (optional)
└─ /storage/event-media/photo1.jpg
   /storage/event-media/photo2.jpg

📱 Instagram Auto-Posting Section ✨ NEW
├─ ☐ Auto-post to Instagram      (UNCHECKED)
│  └─ [Instagram options hidden]
│
└─ ☑ Auto-post to Instagram      (CHECKED)
   └─ Show Instagram Options:
      
      ● Post Immediately
      └─ [Post at 2026-02-05 10:30] 
      
      ○ Schedule Post for Later
      └─ [DateTime picker hidden]

OR

      ● Post Immediately
      └─ [Post at 2026-02-05 10:30]
      
      ○ Schedule Post for Later (SELECTED)
      └─ [DateTime picker SHOWN]
         Date & Time: [2026-02-07________] [14:30____]
         💡 Tip: Minimum 5 minutes from now

Action Buttons
├─ [Create Event] - Primary
└─ [Cancel] - Secondary
```

## 7. Error Handling Flow

```
CREATE EVENT WITH INSTAGRAM AUTO-POST
         ↓
    ┌────────────────────────────┐
    │ VALIDATION CHECKS          │
    └────────────────────────────┘
         ↓
    MULTIPLE CHECKS:
    ├─ ✓ Has event image?
    │  └─ ✗ No → Error: "Event image required"
    │
    ├─ ✓ Scheduled datetime valid?
    │  └─ ✗ No → Error: "Scheduled time must be in future"
    │
    ├─ ✓ Club has Instagram account?
    │  └─ ✗ No → Skipped (no Instagram posting)
    │
    ├─ ✓ Instagram token valid?
    │  └─ ✗ No → Error: "Instagram token expired"
    │
    ├─ ✓ Image file exists?
    │  └─ ✗ No → Error: "Image file not found"
    │
    └─ ✓ All checks pass → Continue
         ↓
    ✅ EVENT CREATED & POSTED/SCHEDULED
```

## 8. Logs & Monitoring

```
┌──────────────────────────────────────────────┐
│ storage/logs/laravel.log                     │
├──────────────────────────────────────────────┤
│ [2026-02-05 10:30:45] local.INFO: Starting  │
│ Instagram post for club...                   │
│                                              │
│ [2026-02-05 10:30:46] local.INFO: Image     │
│ uploaded to ImgBB...                         │
│                                              │
│ [2026-02-05 10:30:47] local.INFO: Event     │
│ successfully posted to Instagram...          │
│                                              │
│ [2026-02-05 10:30:48] local.INFO: Process   │
│ completed successfully                       │
│                                              │
│ ✅ SUCCESS                                    │
└──────────────────────────────────────────────┘
```

## Summary

✅ **Immediate Flow**: Event → Form → DB → Instagram (seconds)
✅ **Scheduled Flow**: Event → Form → DB → (wait) → Command → Instagram
✅ **Cron Flow**: Auto-checks every 5 min → Posts when ready
✅ **Error Handling**: Multi-layer validation with logging
✅ **Database**: Tracks state at each step
