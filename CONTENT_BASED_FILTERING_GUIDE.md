# Content-Based Filtering (CBF) Recommendation System

## Overview

The Content-Based Filtering (CBF) recommendation system analyzes user interests (based on event likes) and recommends relevant events to users. This system improves event discoverability and user engagement by personalizing the event experience.

## Architecture

### Components

1. **ContentBasedFilteringService** (`app/Services/ContentBasedFilteringService.php`)
   - Core algorithm implementation
   - User profile building
   - Event similarity calculation
   - Recommendation generation

2. **RecommendationController** (`app/Http/Controllers/Api/RecommendationController.php`)
   - API endpoints for recommendations
   - Like/unlike functionality
   - Status tracking

3. **EventLike Model** (`app/Models/EventLike.php`)
   - Tracks user interactions
   - Already implemented in the system

## How Content-Based Filtering Works

### User Profile Construction
When a user likes events, the system builds a profile based on event attributes:

```
User Preferences Profile:
├── Categories (e.g., Sports, Tech, Social)
├── Clubs (e.g., Basketball Club, Coding Club)
└── Locations (e.g., Gym, Tech Building)

Each preference weighted by frequency of likes
```

### Event Scoring Algorithm

For each available event, the system calculates:

1. **Profile Relevance Score** (60% weight)
   - How well the event matches user's liked event patterns
   - Category matching: 50%
   - Club matching: 30%
   - Location matching: 20%

2. **Content Similarity Score** (40% weight)
   - How similar the event is to user's liked events
   - Feature matching (category, club, location, status, time)

3. **Final Score = (0.6 × Profile Relevance) + (0.4 × Similarity)**

### Feature Weights

Event features used in similarity calculation:
- **Category**: 35% (most important)
- **Club ID**: 25%
- **Location**: 15%
- **Status**: 10%
- **Temporal (upcoming vs past)**: 15%

## API Endpoints

### Get Personalized Recommendations

```http
GET /api/recommendations?limit=10
Authorization: Bearer {token}
```

**Query Parameters:**
- `limit` (optional): Number of recommendations (default: 10)

**Response:**
```json
{
  "success": true,
  "message": "Recommendations generated successfully",
  "count": 10,
  "data": [
    {
      "id": 1,
      "name": "Basketball Championship",
      "description": "Annual university basketball tournament",
      "category": "Sports",
      "date": "2026-02-15",
      "location": "University Gym",
      "club": "Basketball Club",
      "club_id": 1,
      "event_image": "url/to/image.jpg",
      "likes_count": 45,
      "is_liked": false
    }
  ]
}
```

### Get Similar Events

```http
GET /api/recommendations/similar/{eventId}?limit=5
Authorization: Bearer {token}
```

**Query Parameters:**
- `limit` (optional): Number of similar events (default: 5)

**Response:**
```json
{
  "success": true,
  "message": "Similar events retrieved successfully",
  "count": 5,
  "data": [...]
}
```

### Like an Event

```http
POST /api/events/{eventId}/like
Authorization: Bearer {token}
```

**Request Body:** (empty)

**Response:**
```json
{
  "success": true,
  "message": "Event liked successfully",
  "data": {
    "event_id": 1,
    "likes_count": 46
  }
}
```

**Status Codes:**
- `201`: Event liked successfully
- `409`: Event already liked by user

### Unlike an Event

```http
POST /api/events/{eventId}/unlike
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Event unliked successfully",
  "data": {
    "event_id": 1,
    "likes_count": 45
  }
}
```

### Get User's Liked Events

```http
GET /api/likes
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "User likes retrieved successfully",
  "count": 8,
  "data": [
    {
      "id": 1,
      "name": "Basketball Championship",
      "category": "Sports",
      "date": "2026-02-15",
      "club": "Basketball Club"
    }
  ]
}
```

### Get Event Like Status

```http
GET /api/events/{eventId}/like-status
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "event_id": 1,
    "likes_count": 46,
    "is_liked_by_user": true
  }
}
```

## Usage Examples

### Frontend Implementation (JavaScript/Vue)

```javascript
// Get recommendations for current user
async function getRecommendations() {
  const response = await fetch('/api/recommendations?limit=10', {
    headers: {
      'Authorization': `Bearer ${authToken}`
    }
  });
  const data = await response.json();
  return data.data; // Array of recommended events
}

// Like an event
async function likeEvent(eventId) {
  const response = await fetch(`/api/events/${eventId}/like`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${authToken}`
    }
  });
  return response.json();
}

// Get similar events
async function getSimilarEvents(eventId) {
  const response = await fetch(`/api/recommendations/similar/${eventId}?limit=5`, {
    headers: {
      'Authorization': `Bearer ${authToken}`
    }
  });
  const data = await response.json();
  return data.data; // Array of similar events
}

// Get user's liked events
async function getUserLikes() {
  const response = await fetch('/api/likes', {
    headers: {
      'Authorization': `Bearer ${authToken}`
    }
  });
  const data = await response.json();
  return data.data;
}
```

## Cold Start Problem

When a user has **no likes yet**, the system:
1. Returns a base relevance score of 0.3
2. Uses general event popularity as fallback
3. Recommends events from all categories equally

Once users like events, recommendations become increasingly personalized.

## Model Methods

### Event Model Helper Methods

```php
// Check if event is liked by a user
$event->isLikedBy($user); // Returns bool

// Get percentage of users who liked this event
$event->getLikePercentage(); // Returns float (0-100)
```

### User Model Relationships

```php
// Get all liked events
$user->likedEvents(); // Collection of Events

// Get all likes (EventLike model)
$user->likes(); // Collection of EventLikes
```

## Performance Considerations

1. **Caching** (recommended for production):
   - Cache user profiles (rebuild every 24 hours)
   - Cache recommendation results (15 minutes)
   
2. **Database Queries**:
   - Uses efficient Eloquent relationships
   - Indexes on `user_id` and `event_id` in `event_likes` table

3. **Scalability**:
   - Algorithm complexity: O(n × m) where n = available events, m = liked events
   - Suitable for events up to 10,000+

## Future Enhancements

1. **Hybrid Recommendation**
   - Combine CBF with collaborative filtering
   - Consider what similar users liked

2. **Implicit Feedback**
   - Track views, clicks, attendance
   - Not just explicit likes

3. **Machine Learning**
   - Use historical data to train models
   - More sophisticated pattern recognition

4. **Real-time Updates**
   - Update recommendations as users interact
   - Push notifications for new matches

## Database Schema

The system uses the existing `event_likes` table:

```sql
CREATE TABLE event_likes (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    event_id BIGINT NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (event_id) REFERENCES events(id),
    UNIQUE (user_id, event_id)
);
```

## Troubleshooting

### No recommendations returned
- Check if user has any likes (cold start)
- Verify events have status = 'published'
- Ensure events have future dates

### All recommendations have same score
- User has no likes yet
- All events have identical attributes
- Consider adding more event attributes to feature vector

### Slow recommendations with many events
- Consider implementing caching
- Use pagination with `limit` parameter
- Index database queries

## Testing

```php
// Test with artisan
php artisan tinker

$user = User::find(1);
$service = app(ContentBasedFilteringService::class);
$recommendations = $service->getRecommendations($user, 5);

// Via API
curl -H "Authorization: Bearer {token}" \
  http://localhost/api/recommendations?limit=5
```
