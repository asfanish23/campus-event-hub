# 📸 INSTAGRAM PAGE - VISUAL REFERENCE GUIDE

## Page Layout

```
┌─────────────────────────────────────────────────────────────────┐
│ 📷 Instagram Management                                         │
│ Post and schedule your events to Instagram              ⚙️ Settings
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ ✅ Instagram credentials configured and ready to post!         │
└─────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────┐
│ SEARCH & FILTER SECTION                                          │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│ 🔍 Search:                                                      │
│ [_________________________________]                           │
│                                                                  │
│ Status:           Category:        Instagram Status:  Sort By: │
│ [All Status    ] [All Categories] [All Posts    ]   [Date ▼] │
│                                                                  │
│ [🔎 Apply Filters] [↻ Clear]                                   │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘

Your Events (12)

┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐
│                 │  │                 │  │                 │
│ [EVENT IMAGE]   │  │ [EVENT IMAGE]   │  │ [EVENT IMAGE]   │
│                 │  │                 │  │                 │
├─────────────────┤  ├─────────────────┤  ├─────────────────┤
│ Event Name      │  │ Event Name      │  │ Event Name      │
│                 │  │                 │  │                 │
│ 📅 Feb 07, 2026│  │ 📅 Feb 08, 2026│  │ 📅 Feb 09, 2026│
│ 📍 Location     │  │ 📍 Location     │  │ 📍 Location     │
│ 🏷️ Category    │  │ 🏷️ Category    │  │ 🏷️ Category    │
│                 │  │                 │  │                 │
│ [Upcoming]      │  │ [Running] 📤    │  │ [Completed]     │
│ [📤 Posted]     │  │ [Scheduled]     │  │                 │
│                 │  │                 │  │                 │
│ [📤 Post Now]   │  │ [❌ Cancel]     │  │ [📤 Post Now]   │
│                 │  │                 │  │                 │
└─────────────────┘  └─────────────────┘  └─────────────────┘
```

## Search Bar

```
🔍 Search [________________________]

Help text: Search by event name or location...
Example: "Tech Workshop" or "Auditorium"
```

## Filter Options

### 1. Status Filter
```
Status: [▼ All Status]
├─ All Status
├─ Upcoming
├─ Currently Running
└─ Completed
```

### 2. Category Filter
```
Category: [▼ All Categories]
├─ All Categories
├─ Academic
├─ Sports
├─ Culture
├─ Technology
├─ Volunteer
├─ Leadership
├─ Religious
├─ Entrepreneurship
├─ Arts & Media
└─ Others
```

### 3. Instagram Status Filter
```
Instagram Status: [▼ All Posts]
├─ All Posts
├─ Posted (already posted to Instagram)
├─ Not Posted (not yet posted)
└─ Scheduled (scheduled for future)
```

### 4. Sort By
```
Sort By: [▼ Date (Newest)]
├─ 📅 Date (Newest)
├─ 📅 Date (Oldest)
├─ 🔤 Name (A-Z)
├─ 🔤 Name (Z-A)
├─ ⏰ Created (Newest)
└─ ⏰ Created (Oldest)
```

## Event Card States

### State 1: Not Posted
```
┌──────────────────────┐
│  [EVENT IMAGE]       │
├──────────────────────┤
│ Event Name           │
│                      │
│ 📅 Feb 07, 2026     │
│ 📍 Location          │
│ 🏷️ Category         │
│                      │
│ [Upcoming]           │
│                      │
│ [📤 Post Now]        │ ← Click to post immediately
│ [📅 Schedule Post]   │ ← Click to schedule
└──────────────────────┘
```

### State 2: Posted
```
┌──────────────────────┐
│  [EVENT IMAGE]       │
├──────────────────────┤
│ Event Name           │
│                      │
│ 📅 Feb 07, 2026     │
│ 📍 Location          │
│ 🏷️ Category         │
│                      │
│ [Completed] [📤 Posted]  ← Status badges
│                      │
│ [✅ Already Posted]  │ ← Disabled button
└──────────────────────┘
```

### State 3: Scheduled
```
┌──────────────────────┐
│  [EVENT IMAGE]       │
├──────────────────────┤
│ Event Name           │
│                      │
│ 📅 Feb 07, 2026     │
│ 📍 Location          │
│ 🏷️ Category         │
│                      │
│ [Upcoming] [⏱️ Scheduled]  ← Status badges
│                      │
│ ┌─────────────────┐  │
│ │⏱️ Scheduled for:│  │ ← Scheduled info
│ │Feb 07, 2026 2PM │  │
│ └─────────────────┘  │
│                      │
│ [❌ Cancel Schedule] │ ← Cancel button
└──────────────────────┘
```

### State 4: No Image
```
┌──────────────────────┐
│                      │
│  [🖼️  NO IMAGE]      │ ← Placeholder
│                      │
├──────────────────────┤
│ Event Name           │
│                      │
│ 📅 Feb 07, 2026     │
│ 📍 Location          │
│ 🏷️ Category         │
│                      │
│ [Upcoming]           │
│                      │
│ [🖼️ No Image]        │ ← Disabled button
└──────────────────────┘
```

## Schedule Modal

```
┌─────────────────────────────────────────────┐
│                                             │
│  📅 Schedule Instagram Post                 │
│                                             │
│  Select Date & Time:                        │
│  [2026-02-07_____14:30_____]               │
│                                             │
│  ⚠️ Minimum 5 minutes in the future         │
│                                             │
│  ┌────────────────────────────────────┐    │
│  │ 💡 Tip: Schedule your post when    │    │
│  │ your audience is most active. The  │    │
│  │ system will post automatically at  │    │
│  │ the scheduled time.                │    │
│  └────────────────────────────────────┘    │
│                                             │
│  [✅ Schedule]  [❌ Cancel]                │
│                                             │
└─────────────────────────────────────────────┘
```

## Status Badges

### Event Status (Top Section)
```
[Upcoming]           - Blue badge
[Currently Running]  - Green badge
[Completed]          - Gray badge
```

### Instagram Status (Bottom Section)
```
[📤 Posted]     - Purple badge
[⏱️ Scheduled]  - Yellow badge
```

## Button States

### Post Now (Active)
```
┌──────────────────────┐
│ 📤 Post Now          │ ← Click to post
│ (Gradient pink-red)  │
└──────────────────────┘
```

### Schedule Post (Active)
```
┌──────────────────────┐
│ 📅 Schedule Post     │ ← Click to open modal
│ (Blue background)    │
└──────────────────────┘
```

### Cancel Schedule (Active)
```
┌──────────────────────┐
│ ❌ Cancel Schedule   │ ← Click to cancel
│ (Red background)     │
└──────────────────────┘
```

### Disabled Buttons
```
┌──────────────────────┐
│ ✅ Already Posted    │ ← Grayed out
│ 🖼️ No Image          │
│ ⚙️ Configure First    │
└──────────────────────┘
```

## Filter Combinations Examples

### Example 1: Upcoming Sports Events
```
Status: [Upcoming        ▼]
Category: [Sports        ▼]
Instagram Status: [All Posts ▼]

Result: Shows all upcoming sports events
        that haven't been posted yet
```

### Example 2: Completed and Posted
```
Status: [Completed       ▼]
Instagram Status: [Posted ▼]

Result: Shows all completed events
        that have been posted to Instagram
```

### Example 3: Scheduled Posts
```
Instagram Status: [Scheduled ▼]
Sort By: [Date (Newest) ▼]

Result: Shows all scheduled events
        sorted by date (upcoming first)
```

## Search Examples

```
Search: "Tech"
└─ Shows: Tech Workshop, Technology Summit, etc.

Search: "Auditorium"
└─ Shows: All events at Auditorium

Search: "Feb"
└─ Shows: (No exact match, shows all if partial)

Search: "2026"
└─ Shows: (If event names contain "2026")
```

## Mobile Layout

```
┌─────────────────┐
│ 📷 Instagram    │
│ Management      │
│        ⚙️       │
└─────────────────┘

🔍 Search
[___________]

Status
[All Status ▼]

Category
[All Categories ▼]

Instagram Status
[All Posts ▼]

Sort By
[Date (Newest) ▼]

[🔎 Apply] [↻ Clear]

┌──────────────┐
│ [EVENT IMG]  │
├──────────────┤
│ Event Name   │
│ 📅 Date      │
│ 📍 Location  │
│ 🏷️ Category │
│ [Status]     │
│ [Button]     │
└──────────────┘

┌──────────────┐
│ [EVENT IMG]  │
├──────────────┤
│ Event Name   │
│ ...          │
└──────────────┘
```

## Information Display

### Event Count
```
Your Events (12)  ← Total count of filtered events
```

### Scheduled Info Box
```
┌────────────────────────┐
│ ⏱️ Scheduled for:       │
│ Feb 07, 2026 14:30     │
└────────────────────────┘
```

### Alert Messages

Success:
```
┌────────────────────────────────────┐
│ ✅ Event posted successfully!      │
└────────────────────────────────────┘
```

Error:
```
┌────────────────────────────────────┐
│ ❌ Event must have an image        │
└────────────────────────────────────┘
```

Info:
```
┌────────────────────────────────────┐
│ ⚠️ Minimum 5 minutes in future    │
└────────────────────────────────────┘
```

## Summary

✅ **Search Bar** - Find events by name/location
✅ **4 Filters** - Status, Category, Instagram Status, Sort
✅ **6 Sort Options** - Multiple ways to organize
✅ **Event Cards** - Rich visual display with multiple badges
✅ **Action Buttons** - Context-aware actions
✅ **Schedule Modal** - Easy scheduling interface
✅ **Status Indicators** - Clear visual feedback
✅ **Responsive Design** - Works on all devices
