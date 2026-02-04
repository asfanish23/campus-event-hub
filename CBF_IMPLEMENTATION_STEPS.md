# CBF Display Implementation - Quick Implementation Guide

## 📋 Implementation Checklist

### Phase 1: Backend Updates (Controller & Blade Templates)

#### Step 1: Update StudentDashboardController
**File**: `app/Http/Controllers/Web/StudentDashboardController.php`

Add to `index()` method:
```php
// Add this import at the top
use App\Services\ContentBasedFilteringService;

public function index()
{
    $user = Auth::user();
    
    // ... existing code ...
    
    // NEW: Get recommendations
    $service = app(ContentBasedFilteringService::class);
    $recommendedEvents = $service->getRecommendations($user, 5);
    
    return view('student.dashboard', [
        'user' => $user,
        'allEvents' => $allEvents,
        'upcomingEvents' => $upcomingEvents,
        'recommendedEvents' => $recommendedEvents, // NEW
        'registeredEventIds' => $registeredEventIds,
        'likedEventIds' => $likedEventIds,
        'registeredEventsCount' => $registeredEventsCount,
        'clubs' => $clubs,
    ]);
}
```

Add to `showEvent()` method:
```php
public function showEvent(Event $event)
{
    // ... existing code ...
    
    // NEW: Get similar events
    $service = app(ContentBasedFilteringService::class);
    $similarEvents = $service->getSimilarEvents($event, 4);
    
    return view('student.event-details', [
        'event' => $event,
        'isRegistered' => $isRegistered,
        'isLiked' => $isLiked,
        'likeCount' => $likeCount,
        'similarEvents' => $similarEvents, // NEW
    ]);
}
```

#### Step 2: Update Dashboard View
**File**: `resources/views/student/dashboard.blade.php`

Add after the stats section (find where to insert):
```blade
<!-- ✨ Recommended Events Section -->
@if(auth()->user()->likes()->exists() && isset($recommendedEvents) && $recommendedEvents->count() > 0)
    <div class="recommended-section">
        <div class="section-header">
            <h2>✨ Recommended For You</h2>
            <p>Based on events you've liked</p>
        </div>
        
        <div class="events-grid">
            @foreach($recommendedEvents as $event)
                <div class="event-card recommended-card">
                    <div class="event-image">
                        <img src="{{ $event->event_image ?? 'https://via.placeholder.com/300x200' }}" alt="{{ $event->name }}">
                        <button class="like-btn" 
                                onclick="toggleLike(event, {{ $event->id }})"
                                data-event-id="{{ $event->id }}"
                                data-liked="{{ $event->isLikedBy(auth()->user()) ? 'true' : 'false' }}">
                            @if($event->isLikedBy(auth()->user()))
                                ❤️
                            @else
                                🤍
                            @endif
                        </button>
                    </div>
                    
                    <div class="event-info">
                        <h3>{{ $event->name }}</h3>
                        
                        <div class="event-meta">
                            <span class="badge badge-category">{{ $event->category }}</span>
                            <span class="badge badge-club">{{ $event->club->name }}</span>
                        </div>
                        
                        <div class="event-details">
                            <p class="event-date">📅 {{ $event->date->format('M d, Y') }}</p>
                            <p class="event-time">⏰ {{ $event->start_time->format('H:i') }}</p>
                            <p class="event-location">📍 {{ $event->location }}</p>
                        </div>
                        
                        <div class="event-stats">
                            <span class="stat-likes">❤️ {{ $event->likes()->count() }} likes</span>
                        </div>
                        
                        <div class="event-actions">
                            <a href="{{ route('student.event.show', $event->id) }}" class="btn btn-primary">
                                View Event
                            </a>
                            @if(!in_array($event->id, $registeredEventIds ?? []))
                                <form action="{{ route('student.event.register', $event->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success">Register</button>
                                </form>
                            @else
                                <span class="badge badge-success">✓ Registered</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@elseif(!auth()->user()->likes()->exists())
    <div class="cold-start-section">
        <div class="empty-state">
            <h3>👆 Like Events to Get Personalized Recommendations!</h3>
            <p>As you like events, we'll recommend similar ones just for you.</p>
        </div>
    </div>
@endif
```

#### Step 3: Update Event Details View
**File**: `resources/views/student/event-details.blade.php`

Add at the bottom before closing tags:
```blade
<!-- 🔗 Similar Events Section -->
@if(isset($similarEvents) && $similarEvents->count() > 0)
    <div class="similar-events-section">
        <h2>🔗 Similar Events You Might Like</h2>
        <p>Check out other events like this one</p>
        
        <div class="events-carousel">
            @foreach($similarEvents as $similar)
                <div class="event-card-small">
                    <div class="card-image">
                        <img src="{{ $similar->event_image ?? 'https://via.placeholder.com/200x150' }}" 
                             alt="{{ $similar->name }}">
                    </div>
                    <div class="card-info">
                        <h4>{{ Str::limit($similar->name, 25) }}</h4>
                        <p class="category">{{ $similar->category }}</p>
                        <p class="date">{{ $similar->date->format('M d') }}</p>
                        <a href="{{ route('student.event.show', $similar->id) }}" class="link">
                            View Event →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
```

---

### Phase 2: Styling (CSS)

**File**: `resources/css/components/recommendations.css` (create new file)

```css
/* ===== RECOMMENDED SECTION ===== */
.recommended-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 30px;
    border-radius: 12px;
    margin: 30px 0;
    color: white;
}

.section-header {
    margin-bottom: 25px;
    text-align: center;
}

.section-header h2 {
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 10px 0;
}

.section-header p {
    color: rgba(255, 255, 255, 0.9);
    margin: 0;
    font-size: 14px;
}

/* Events Grid */
.events-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

/* Recommended Event Card */
.event-card.recommended-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.event-card.recommended-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}

/* Event Image Container */
.event-image {
    position: relative;
    width: 100%;
    height: 180px;
    overflow: hidden;
    background: #f0f0f0;
}

.event-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.event-card.recommended-card:hover .event-image img {
    transform: scale(1.05);
}

/* Like Button */
.like-btn {
    position: absolute;
    top: 12px;
    right: 12px;
    background: rgba(255, 255, 255, 0.95);
    border: 2px solid #ddd;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    font-size: 24px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.like-btn:hover {
    background: white;
    border-color: #ff4458;
    transform: scale(1.1);
}

.like-btn[data-liked="true"] {
    background: #ff4458;
    border-color: #ff4458;
}

/* Event Info */
.event-info {
    padding: 16px;
}

.event-info h3 {
    margin: 0 0 10px 0;
    font-size: 18px;
    color: #333;
    font-weight: 600;
}

.event-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 12px;
}

.badge {
    font-size: 11px;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: 500;
}

.badge-category {
    background: #e3f2fd;
    color: #1976d2;
}

.badge-club {
    background: #f3e5f5;
    color: #6a1b9a;
}

.badge-success {
    background: #c8e6c9;
    color: #2e7d32;
}

/* Event Details */
.event-details {
    font-size: 13px;
    color: #666;
    margin: 10px 0;
}

.event-date,
.event-time,
.event-location {
    margin: 4px 0;
}

.event-stats {
    padding: 10px 0;
    border-top: 1px solid #eee;
    border-bottom: 1px solid #eee;
    margin: 10px 0;
    font-size: 13px;
}

.stat-likes {
    color: #ff4458;
    font-weight: 500;
}

/* Event Actions */
.event-actions {
    display: flex;
    gap: 8px;
    margin-top: 12px;
}

.btn {
    flex: 1;
    padding: 8px 12px;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    text-align: center;
}

.btn-primary {
    background: #1976d2;
    color: white;
}

.btn-primary:hover {
    background: #1565c0;
    transform: translateY(-2px);
}

.btn-success {
    background: #4caf50;
    color: white;
}

.btn-success:hover {
    background: #45a049;
}

/* ===== SIMILAR EVENTS SECTION ===== */
.similar-events-section {
    margin-top: 50px;
    padding: 30px 0;
    border-top: 2px solid #eee;
}

.similar-events-section h2 {
    font-size: 24px;
    margin: 0 0 8px 0;
    color: #333;
}

.similar-events-section p {
    color: #666;
    margin: 0 0 20px 0;
}

.events-carousel {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 16px;
    padding-bottom: 20px;
    overflow-x: auto;
}

.event-card-small {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    cursor: pointer;
}

.event-card-small:hover {
    transform: translateY(-6px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
}

.card-image {
    width: 100%;
    height: 140px;
    overflow: hidden;
    background: #f0f0f0;
}

.card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.event-card-small:hover .card-image img {
    transform: scale(1.05);
}

.card-info {
    padding: 12px;
}

.card-info h4 {
    margin: 0 0 8px 0;
    font-size: 14px;
    color: #333;
    font-weight: 600;
}

.card-info .category {
    display: inline-block;
    font-size: 11px;
    color: #666;
    background: #f0f0f0;
    padding: 3px 6px;
    border-radius: 3px;
    margin-bottom: 6px;
}

.card-info .date {
    font-size: 12px;
    color: #999;
}

.link {
    color: #1976d2;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    transition: color 0.3s ease;
}

.link:hover {
    color: #1565c0;
    text-decoration: underline;
}

/* ===== COLD START STATE ===== */
.cold-start-section {
    background: linear-gradient(135deg, #f5af19 0%, #f12711 100%);
    padding: 40px;
    border-radius: 12px;
    margin: 30px 0;
    text-align: center;
    color: white;
}

.empty-state {
    margin: 0;
}

.empty-state h3 {
    font-size: 22px;
    margin: 0 0 10px 0;
    font-weight: 700;
}

.empty-state p {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.9);
    margin: 0;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .recommended-section {
        padding: 20px;
    }
    
    .section-header h2 {
        font-size: 22px;
    }
    
    .events-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 16px;
    }
    
    .event-actions {
        flex-direction: column;
    }
    
    .events-carousel {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .recommended-section {
        padding: 16px;
    }
    
    .events-grid {
        grid-template-columns: 1fr;
    }
    
    .section-header h2 {
        font-size: 18px;
    }
    
    .events-carousel {
        grid-template-columns: 1fr;
    }
}
```

---

### Phase 3: JavaScript (Interactivity)

**File**: `resources/js/recommendations.js` (create new file)

```javascript
// Toggle Like Button
function toggleLike(event, eventId) {
    event.preventDefault();
    
    const button = event.target.closest('.like-btn');
    const isLiked = button.getAttribute('data-liked') === 'true';
    const endpoint = isLiked ? 'unlike' : 'like';
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    
    fetch(`/student/event/${eventId}/${endpoint}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success || data.message) {
            // Update button state
            const newState = !isLiked;
            button.setAttribute('data-liked', newState);
            button.textContent = newState ? '❤️' : '🤍';
            
            // Show notification
            showNotification(
                newState ? 'Event liked! ❤️' : 'Event unliked!'
            );
            
            // Optional: Refresh recommendations
            // refreshRecommendations();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Something went wrong', 'error');
    });
}

// Show Toast Notification
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `toast toast-${type}`;
    notification.textContent = message;
    
    // Style the notification
    Object.assign(notification.style, {
        position: 'fixed',
        bottom: '20px',
        right: '20px',
        backgroundColor: type === 'success' ? '#4caf50' : '#f44336',
        color: 'white',
        padding: '16px 20px',
        borderRadius: '6px',
        zIndex: '9999',
        animation: 'slideIn 0.3s ease',
        fontWeight: '500'
    });
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Refresh recommendations (optional)
function refreshRecommendations() {
    location.reload();
    // Or use AJAX to reload only the section
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
```

---

### Phase 4: Include in Layout

**File**: `resources/views/layouts/app.blade.php` or main layout

Add to the `<head>` section:
```blade
<link rel="stylesheet" href="{{ asset('css/components/recommendations.css') }}">
```

Add before closing `</body>`:
```blade
<script src="{{ asset('js/recommendations.js') }}"></script>
```

---

## ✅ Implementation Verification Checklist

- [ ] Updated `StudentDashboardController.php` with CBF service calls
- [ ] Updated `dashboard.blade.php` with recommendations section
- [ ] Updated `event-details.blade.php` with similar events
- [ ] Created `recommendations.css` with all styles
- [ ] Created `recommendations.js` with like toggle functionality
- [ ] Added CSS include to layout
- [ ] Added JS include to layout
- [ ] Tested on local development
  - [ ] Verified cold start (new user with no likes)
  - [ ] Verified recommendations with likes
  - [ ] Tested like/unlike button toggle
  - [ ] Tested register from recommendations
  - [ ] Tested similar events display
- [ ] Mobile responsive tested
- [ ] Performance verified (load times)

---

## 🚀 Deployment Steps

1. **Update Controller**
   ```bash
   # Edit StudentDashboardController
   nano app/Http/Controllers/Web/StudentDashboardController.php
   ```

2. **Update Views**
   ```bash
   # Edit dashboard and event details
   nano resources/views/student/dashboard.blade.php
   nano resources/views/student/event-details.blade.php
   ```

3. **Add CSS**
   ```bash
   # Create new CSS file
   touch resources/css/components/recommendations.css
   ```

4. **Add JavaScript**
   ```bash
   # Create new JS file
   touch resources/js/recommendations.js
   ```

5. **Update Layout**
   ```bash
   # Include CSS and JS in layout
   nano resources/views/layouts/app.blade.php
   ```

6. **Test**
   ```bash
   # Access dashboard
   http://localhost/student/dashboard
   ```

---

## 📝 Expected Results

After implementation, students will see:

✅ **On Dashboard**
- Personalized "Recommended For You" section
- 5 recommended event cards
- Like button on each card
- Register button
- View Event link

✅ **On Event Details**
- "Similar Events" section at bottom
- 3-4 event cards
- Quick link to view each similar event

✅ **Interactivity**
- Like/Unlike toggle works
- Registers directly from recommendations
- Toast notifications for user feedback
- Responsive on mobile

---

**You're ready to implement! Start with Phase 1 (Backend Updates)**
