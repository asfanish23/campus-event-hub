# CBF Integration - Display Strategy for Student Discovery

## Current System Structure

Based on your CampusEventHub, here's where recommendations will be displayed:

```
Student Experience Flow:
├── Dashboard (index) → Shows upcoming events
├── Calendar → View events by date
├── Event Details → Individual event view
├── Clubs → Browse clubs and their events
└── Archive → Past events
```

---

## 🎯 Integration Points for Recommendations

### 1. **Student Dashboard** (Primary Location)
**File**: `resources/views/student/dashboard.blade.php`
**Current**: Shows upcoming events

**Add Recommendations Section**:
```
Dashboard Layout:
┌─────────────────────────────────────┐
│  Student Dashboard                  │
├─────────────────────────────────────┤
│                                     │
│  📊 Your Stats                      │
│  ├─ Events Registered: 3            │
│  ├─ Events Liked: 5                 │
│  └─ Latest Activity                 │
│                                     │
├─────────────────────────────────────┤
│  🎯 RECOMMENDED FOR YOU ⭐ NEW!     │
│  ├─ Event Card 1 (93% match)       │
│  ├─ Event Card 2 (87% match)       │
│  ├─ Event Card 3 (82% match)       │
│  └─ View More →                    │
│                                     │
├─────────────────────────────────────┤
│  📅 Upcoming Events                 │
│  ├─ Event List (6 items)           │
│  └─ View All →                     │
│                                     │
├─────────────────────────────────────┤
│  🔥 Trending Events                 │
│  ├─ Popular events by likes        │
│  └─ View More →                    │
│                                     │
└─────────────────────────────────────┘
```

### 2. **Event Details Page** (Secondary Location)
**File**: `resources/views/student/event-details.blade.php`
**Current**: Shows event info, register button, like button

**Add Similar Events Section**:
```
Event Details:
┌─────────────────────────────────┐
│  Event: Basketball Championship  │
├─────────────────────────────────┤
│  [Event Image]                  │
│  Description, Date, Location... │
│  [Register] [❤️ Like]           │
├─────────────────────────────────┤
│  📌 Similar Events              │
│  (Show 3-4 events like this)   │
│  ├─ Event A (Similar)          │
│  ├─ Event B (Similar)          │
│  └─ View More →                │
│                                 │
└─────────────────────────────────┘
```

### 3. **Calendar View** (Tertiary Location)
**File**: `resources/views/student/calendar.blade.php`
**Current**: Shows events by date

**Add Filter/Highlight**:
```
Calendar with Recommendations:
┌─────────────────────────────────┐
│  Filter: [All] [⭐ Recommended] │
│                                 │
│  Mon | Tue | Wed | Thu | Fri   │
│  ─────────────────────────────  │
│  1   │ 2  │ 3  │ 4  │ 5        │
│      │ ⭐ │    │ ⭐ │          │
│  ... (highlight recommended)   │
└─────────────────────────────────┘
```

### 4. **Clubs Page** (Bonus Location)
**File**: `resources/views/student/clubs.blade.php`

**Add Club Recommendations**:
```
Clubs:
├─ Clubs You Follow
├─ Recommended Clubs (based on liked events)
└─ All Clubs
```

---

## 💻 Implementation Steps

### Step 1: Update StudentDashboardController

```php
public function index()
{
    $user = Auth::user();
    
    // Existing code...
    $upcomingEvents = Event::where('date', '>=', now()->startOfDay())
        ->orderBy('date', 'asc')
        ->limit(6)
        ->get();
    
    // NEW: Get recommendations
    $service = app(\App\Services\ContentBasedFilteringService::class);
    $recommendedEvents = $service->getRecommendations($user, 5);
    
    return view('student.dashboard', [
        'user' => $user,
        'allEvents' => $allEvents,
        'upcomingEvents' => $upcomingEvents,
        'recommendedEvents' => $recommendedEvents, // NEW!
        'registeredEventIds' => $registeredEventIds,
        'likedEventIds' => $likedEventIds,
        'registeredEventsCount' => $registeredEventsCount,
        'clubs' => $clubs,
    ]);
}

public function showEvent(Event $event)
{
    $user = Auth::user();
    $isRegistered = StudentEventRegistration::where('user_id', $user->id)
        ->where('event_id', $event->id)
        ->exists();

    $isLiked = EventLike::where('user_id', $user->id)
        ->where('event_id', $event->id)
        ->exists();

    $likeCount = $event->likes()->count();

    // NEW: Get similar events
    $service = app(\App\Services\ContentBasedFilteringService::class);
    $similarEvents = $service->getSimilarEvents($event, 4);

    return view('student.event-details', [
        'event' => $event,
        'isRegistered' => $isRegistered,
        'isLiked' => $isLiked,
        'likeCount' => $likeCount,
        'similarEvents' => $similarEvents, // NEW!
    ]);
}
```

### Step 2: Update Dashboard View

**File**: `resources/views/student/dashboard.blade.php`

Add this section (place it above or after upcoming events):

```blade
<!-- Recommended Events Section -->
@if(auth()->user()->likes()->exists())
    <div class="recommended-section">
        <div class="section-header">
            <h2>✨ Recommended For You</h2>
            <p>Based on events you've liked</p>
        </div>
        
        <div class="events-grid">
            @forelse($recommendedEvents as $event)
                <div class="event-card recommended">
                    <!-- Event Image -->
                    <div class="event-image">
                        <img src="{{ $event->event_image }}" alt="{{ $event->name }}">
                        
                        <!-- Like Button -->
                        <button class="like-btn" 
                                onclick="toggleLike({{ $event->id }})"
                                data-liked="{{ in_array($event->id, $likedEventIds) ? 'true' : 'false' }}">
                            ❤️
                        </button>
                    </div>
                    
                    <!-- Event Info -->
                    <div class="event-info">
                        <h3>{{ $event->name }}</h3>
                        
                        <div class="event-meta">
                            <span class="category">{{ $event->category }}</span>
                            <span class="club">{{ $event->club->name }}</span>
                        </div>
                        
                        <div class="event-details">
                            <p>📅 {{ $event->date->format('M d, Y') }}</p>
                            <p>⏰ {{ $event->start_time->format('H:i') }}</p>
                            <p>📍 {{ $event->location }}</p>
                        </div>
                        
                        <div class="event-stats">
                            <span>❤️ {{ $event->likes()->count() }} likes</span>
                        </div>
                        
                        <div class="event-actions">
                            <a href="{{ route('student.event.show', $event->id) }}" class="btn-view">
                                View Event
                            </a>
                            @if(!in_array($event->id, $registeredEventIds))
                                <form action="{{ route('student.event.register', $event->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn-register">Register</button>
                                </form>
                            @else
                                <span class="badge-registered">✓ Registered</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <p>👆 Like some events to get personalized recommendations!</p>
                </div>
            @endforelse
        </div>
    </div>
@endif
```

### Step 3: Update Event Details View

**File**: `resources/views/student/event-details.blade.php`

Add this section at the bottom:

```blade
<!-- Similar Events Section -->
@if($similarEvents->count() > 0)
    <div class="similar-events-section">
        <h2>🔗 Similar Events</h2>
        <p>You might also be interested in these</p>
        
        <div class="events-carousel">
            @foreach($similarEvents as $similar)
                <div class="event-card-small">
                    <img src="{{ $similar->event_image }}" alt="{{ $similar->name }}">
                    <h4>{{ $similar->name }}</h4>
                    <p class="category">{{ $similar->category }}</p>
                    <a href="{{ route('student.event.show', $similar->id) }}" class="link">
                        View Event →
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endif
```

---

## 🎨 UI/UX Design Recommendations

### Visual Hierarchy

```
RECOMMENDED SECTION PLACEMENT:

Option 1: After User Stats, Before Upcoming Events (RECOMMENDED)
┌─────────────────────────┐
│ Stats                   │
├─────────────────────────┤ ⭐ BEST - Prominent
│ ✨ Recommended Events   │
├─────────────────────────┤
│ 📅 Upcoming Events      │
└─────────────────────────┘

Option 2: Sidebar Widget
┌──────────────┐
│ Dashboard    │ ┌──────────────┐
│              │ │ Recommended  │
│              │ │ ✨ Event A   │
│              │ │ ✨ Event B   │
└──────────────┘ └──────────────┘

Option 3: Tab View
┌──────────────────────┐
│ [📅 All] [✨ Recommended] │
├──────────────────────┤
│ Events list by tab   │
└──────────────────────┘
```

### Styling Guide

```css
/* Recommended Section */
.recommended-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 30px;
    border-radius: 12px;
    margin: 20px 0;
    color: white;
}

.section-header {
    margin-bottom: 20px;
}

.section-header h2 {
    font-size: 24px;
    margin: 0;
}

.section-header p {
    color: rgba(255,255,255,0.8);
    margin: 5px 0 0 0;
}

/* Event Cards in Recommended */
.event-card.recommended {
    transition: transform 0.3s, box-shadow 0.3s;
    background: white;
    border-radius: 8px;
    overflow: hidden;
}

.event-card.recommended:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.15);
}

/* Like Button */
.like-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(255,255,255,0.9);
    border: none;
    border-radius: 50%;
    width: 45px;
    height: 45px;
    font-size: 24px;
    cursor: pointer;
    transition: all 0.3s;
}

.like-btn:hover {
    background: white;
    transform: scale(1.1);
}

.like-btn[data-liked="true"] {
    background: #ff4458;
    color: white;
}
```

---

## 📱 Display Strategy by Page

### Dashboard (student.dashboard)
```
Sections in Order:
1. Welcome Banner
2. Quick Stats (Registered: 3, Liked: 5)
3. ⭐ RECOMMENDED EVENTS (NEW)
   - 5 personalized event cards
   - Based on user's likes
   - Sortable, filterable

4. Upcoming Events
   - All upcoming events
   - Traditional view

5. Trending Events
   - Most liked events

6. Clubs Section
```

### Event Details (student.event-details)
```
Sections in Order:
1. Event Header
   - Image
   - Title
   - Category, Club, Date, Time, Location

2. Event Description

3. Registration Section
   - [Register] or [Registered ✓]
   - [❤️ Like] button

4. Event Stats
   - Registrations: X
   - Likes: X

5. 🔗 SIMILAR EVENTS (NEW)
   - 3-4 similar events carousel
   - Horizontal scroll
   - Quick preview

6. Reviews (if exists)

7. Share Options
```

### Calendar (student.calendar)
```
Sections:
1. Calendar View
   - Add filter: [All Events] vs [Recommended Only]
   - Highlight recommended with ⭐ icon

2. Day/Week/Month toggle

3. Event List below calendar
```

---

## 🔄 User Journey

### New Student (Cold Start)
```
1. Logs in → Dashboard
   ↓
2. Sees "Like some events to get recommendations"
   ↓
3. Browses upcoming events
   ↓
4. Likes 3-5 events
   ↓
5. Refreshes dashboard
   ↓
6. Sees personalized recommendations! ✨
```

### Returning Student
```
1. Logs in → Dashboard
   ↓
2. Sees personalized recommendations immediately
   ↓
3. Can register or like new events
   ↓
4. Recommendations update with new preferences
```

---

## 🛠️ Technical Implementation Details

### JavaScript for Like Button Toggle

```javascript
// Add to your student dashboard JS
function toggleLike(eventId) {
    const button = event.target.closest('.like-btn');
    const isLiked = button.getAttribute('data-liked') === 'true';
    
    fetch(`/api/events/${eventId}/${isLiked ? 'unlike' : 'like'}`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${authToken}`,
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update button state
            button.setAttribute('data-liked', !isLiked);
            button.textContent = !isLiked ? '❤️' : '🤍';
            
            // Show feedback
            showNotification('Event ' + (!isLiked ? 'liked' : 'unliked') + '!');
            
            // Refresh recommendations (optional)
            // location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}
```

### Load Recommendations via AJAX

```javascript
// Load recommendations when page loads
document.addEventListener('DOMContentLoaded', function() {
    fetch('/api/recommendations?limit=5', {
        headers: {
            'Authorization': `Bearer ${authToken}`
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderRecommendations(data.data);
        }
    });
});

function renderRecommendations(events) {
    const container = document.querySelector('.events-grid');
    
    events.forEach(event => {
        const card = createEventCard(event);
        container.appendChild(card);
    });
}

function createEventCard(event) {
    return `
        <div class="event-card recommended">
            <div class="event-image">
                <img src="${event.event_image}" alt="${event.name}">
                <button class="like-btn" onclick="toggleLike(${event.id})" 
                        data-liked="${event.is_liked}">
                    ${event.is_liked ? '❤️' : '🤍'}
                </button>
            </div>
            <div class="event-info">
                <h3>${event.name}</h3>
                <p class="category">${event.category}</p>
                <p class="club">${event.club}</p>
                <p class="date">📅 ${event.date}</p>
                <p class="location">📍 ${event.location}</p>
                <p class="likes">❤️ ${event.likes_count}</p>
                <a href="/student/event/${event.id}" class="btn-view">
                    View Event
                </a>
            </div>
        </div>
    `;
}
```

---

## 📊 Analytics & Tracking

Track these metrics to improve recommendations:

```php
// In StudentDashboardController
$metrics = [
    'recommendations_shown' => $recommendedEvents->count(),
    'user_likes_count' => $likedEventIds->count(),
    'registered_count' => $registeredEventsCount,
    'click_through_rate' => 'to be tracked', // clicks on recommended events
];
```

---

## 🎯 Summary: Where Recommendations Appear

| Location | Feature | Priority |
|----------|---------|----------|
| **Dashboard** | Recommended For You section | ⭐⭐⭐ HIGHEST |
| **Event Details** | Similar Events carousel | ⭐⭐ HIGH |
| **Calendar** | Filter & highlight recommended | ⭐⭐ HIGH |
| **Clubs Page** | Recommended clubs | ⭐ MEDIUM |
| **Sidebar Widget** | Quick recommendations | ⭐ MEDIUM |

---

## ✅ Implementation Checklist

- [ ] Add recommendations to StudentDashboardController
- [ ] Update dashboard.blade.php with recommendations section
- [ ] Update event-details.blade.php with similar events
- [ ] Add CSS styling for recommendation cards
- [ ] Add JavaScript for like button toggle
- [ ] Test cold start (new user with no likes)
- [ ] Test with existing users
- [ ] Monitor recommendation quality
- [ ] Gather user feedback
- [ ] Iterate and improve

---

## 🚀 Next Steps

1. **Update Controller** - Add CBF service calls
2. **Update Views** - Add recommendation sections
3. **Add Styling** - CSS for visual design
4. **Add JavaScript** - Like button toggle and AJAX
5. **Test** - With real users
6. **Monitor** - Track engagement metrics
7. **Improve** - Adjust based on feedback

Would you like me to implement any of these sections? I can:
- Create the controller updates
- Create the view updates  
- Create the CSS styling
- Create the JavaScript functionality

Let me know what you'd like me to focus on!
