# Content-Based Filtering - Testing Guide

## Quick Test Setup

### Prerequisites
- User account with token
- Some events in database
- API client (Postman, cURL, or Thunder Client)

## Step-by-Step Testing

### 1. Get Authentication Token

#### Via API
```bash
POST /api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}

Response:
{
  "token": "1|XXXXXXXXXXXXXXXXXXXXX",
  "user": {...}
}
```

#### Save Token
```bash
export TOKEN="1|XXXXXXXXXXXXXXXXXXXXX"
```

### 2. Test Like Functionality

#### Like First Event
```bash
curl -X POST \
  -H "Authorization: Bearer $TOKEN" \
  http://localhost/api/events/1/like

Expected Response (201):
{
  "success": true,
  "message": "Event liked successfully",
  "data": {
    "event_id": 1,
    "likes_count": 1
  }
}
```

#### Like More Events (Build Profile)
```bash
# Like event 2
curl -X POST \
  -H "Authorization: Bearer $TOKEN" \
  http://localhost/api/events/2/like

# Like event 3
curl -X POST \
  -H "Authorization: Bearer $TOKEN" \
  http://localhost/api/events/3/like

# Like event 5
curl -X POST \
  -H "Authorization: Bearer $TOKEN" \
  http://localhost/api/events/5/like
```

### 3. View Liked Events

```bash
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost/api/likes

Response:
{
  "success": true,
  "message": "User likes retrieved successfully",
  "count": 4,
  "data": [
    {
      "id": 1,
      "name": "Basketball Championship",
      "category": "Sports",
      "date": "2026-02-15",
      "club": "Basketball Club"
    },
    ...
  ]
}
```

### 4. Get Recommendations

#### Default (10 events)
```bash
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost/api/recommendations

Response:
{
  "success": true,
  "message": "Recommendations generated successfully",
  "count": 8,
  "data": [
    {
      "id": 4,
      "name": "Football Tournament",
      "description": "Annual football tournament...",
      "category": "Sports",
      "date": "2026-02-20",
      "location": "Stadium",
      "club": "Football Club",
      "club_id": 2,
      "event_image": "url...",
      "likes_count": 23,
      "is_liked": false
    },
    ...
  ]
}
```

#### Custom Limit (5 events)
```bash
curl -H "Authorization: Bearer $TOKEN" \
  "http://localhost/api/recommendations?limit=5"
```

### 5. Get Similar Events

#### Find Events Similar to Event #1
```bash
curl -H "Authorization: Bearer $TOKEN" \
  "http://localhost/api/recommendations/similar/1?limit=5"

Response:
{
  "success": true,
  "message": "Similar events retrieved successfully",
  "count": 5,
  "data": [
    {
      "id": 4,
      "name": "Football Tournament",
      "category": "Sports",
      ...
    },
    {
      "id": 7,
      "name": "Volleyball Tournament",
      "category": "Sports",
      ...
    },
    ...
  ]
}
```

### 6. Check Like Status

```bash
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost/api/events/1/like-status

Response:
{
  "success": true,
  "data": {
    "event_id": 1,
    "likes_count": 1,
    "is_liked_by_user": true
  }
}
```

### 7. Unlike an Event

```bash
curl -X POST \
  -H "Authorization: Bearer $TOKEN" \
  http://localhost/api/events/1/unlike

Response:
{
  "success": true,
  "message": "Event unliked successfully",
  "data": {
    "event_id": 1,
    "likes_count": 0
  }
}
```

## Postman Collection

### Import These Requests

#### 1. Login
```
POST /api/login
Headers: Content-Type: application/json
Body: {
  "email": "user@example.com",
  "password": "password"
}
```
Set variable: `token = response.token`

#### 2. Get Recommendations
```
GET /api/recommendations?limit=10
Headers: Authorization: Bearer {{token}}
```

#### 3. Get Similar Events
```
GET /api/recommendations/similar/1?limit=5
Headers: Authorization: Bearer {{token}}
```

#### 4. Like Event
```
POST /api/events/1/like
Headers: Authorization: Bearer {{token}}
```

#### 5. Unlike Event
```
POST /api/events/1/unlike
Headers: Authorization: Bearer {{token}}
```

#### 6. View Likes
```
GET /api/likes
Headers: Authorization: Bearer {{token}}
```

#### 7. Check Like Status
```
GET /api/events/1/like-status
Headers: Authorization: Bearer {{token}}
```

## Testing Scenarios

### Scenario 1: New User (Cold Start)
**Objective**: Test recommendation quality with no likes

1. Create new user account
2. Call `GET /api/recommendations`
3. Should return popular events across all categories
4. Expected: 8-10 diverse events

### Scenario 2: Building User Profile
**Objective**: Test profile building as user likes events

1. Start with new user
2. Like 3-5 events in same category (e.g., Sports)
3. Call `GET /api/recommendations`
4. Expected: More sports events recommended than other categories

### Scenario 3: Category Preference
**Objective**: Test category-based recommendations

1. Like events with categories:
   - 3× Sports events
   - 2× Tech events
   - 1× Social event
2. Call `GET /api/recommendations`
3. Expected: Majority (50%+) should be Sports, then Tech, then Social

### Scenario 4: Similar Events
**Objective**: Test similar event discovery

1. Like event #1 (Sports, Basketball Club, Gym)
2. Call `GET /api/recommendations/similar/1`
3. Expected: Events with similar:
   - Category (Sports)
   - Or from same club
   - Or at same location

### Scenario 5: Like/Unlike Toggle
**Objective**: Test like state management

1. Like event #5
2. Get like status → should show `is_liked: true`
3. Unlike event #5
4. Get like status → should show `is_liked: false`
5. Get user likes → event #5 should not be in list

## Expected Behavior

### Successful Response
```json
{
  "success": true,
  "message": "...",
  "data": {...}
}
```

### Error Response (Already Liked)
```json
{
  "success": false,
  "message": "Event already liked",
  "status": 409
}
```

### Error Response (Not Authenticated)
```json
{
  "message": "Unauthenticated."
}
```

## Performance Testing

### Measure Response Time
```bash
time curl -H "Authorization: Bearer $TOKEN" \
  http://localhost/api/recommendations?limit=50
```

### Expected Performance
- Cold start (no likes): ~50-100ms
- With 5 likes: ~100-150ms
- With 50+ likes: ~200-300ms

### Scale Test
```bash
# Run recommendation 100 times
for i in {1..100}; do
  curl -s -H "Authorization: Bearer $TOKEN" \
    http://localhost/api/recommendations?limit=5 > /dev/null
done
```

## Database Verification

### Via Tinker
```php
php artisan tinker

# Check likes were stored
EventLike::where('user_id', 1)->count();
# Expected: 4 (if you liked 4 events)

# Check specific like
EventLike::where('user_id', 1)->where('event_id', 1)->exists();
# Expected: true

# View user's liked events
User::find(1)->likedEvents()->get();
# Shows all liked events for user 1
```

### Via MySQL
```sql
-- Check likes table
SELECT COUNT(*) FROM event_likes;

-- Check user's likes
SELECT e.name, e.category 
FROM event_likes el
JOIN events e ON el.event_id = e.id
WHERE el.user_id = 1;

-- Check event like count
SELECT event_id, COUNT(*) as likes
FROM event_likes
GROUP BY event_id
ORDER BY likes DESC
LIMIT 10;
```

## Troubleshooting

### Issue: No Recommendations
**Possible Causes**:
- No likes yet (cold start) → Expected behavior
- No published events → Check events status
- All recommendations already liked → Like different events

**Solution**:
1. Like at least 1 event
2. Ensure events have status = 'published'
3. Check event dates are in future

### Issue: Same Recommendations Every Time
**Possible Causes**:
- Limited event variety
- All events have similar attributes
- User preferences too narrow

**Solution**:
1. Add more varied events
2. Like events in different categories
3. Verify event attributes (category, location, club)

### Issue: 401 Unauthorized
**Possible Causes**:
- Token expired
- Token not in header
- Wrong token format

**Solution**:
1. Get fresh token from login
2. Include header: `Authorization: Bearer {token}`
3. Check token format (starts with number|)

### Issue: 404 Not Found
**Possible Causes**:
- Event doesn't exist
- Event ID is wrong

**Solution**:
1. Verify event ID exists
2. Check database for event
3. Use valid event ID from /api/events list

## Next Steps After Testing

1. **Verify Results**
   - Check that recommendations match user preferences
   - Ensure similar events are actually similar

2. **Check Accuracy**
   - Do sports-loving users get sports events?
   - Do recommended events get liked by users?

3. **Performance Check**
   - Response time acceptable?
   - Scales well with more events?

4. **Frontend Integration**
   - Add like button to UI
   - Display recommendations on dashboard
   - Show similar events on detail page

## Quick Test Commands

```bash
# Set token
TOKEN="your_token_here"

# Get recommendations
curl -H "Authorization: Bearer $TOKEN" http://localhost/api/recommendations

# Like event 1
curl -X POST -H "Authorization: Bearer $TOKEN" http://localhost/api/events/1/like

# Get similar to event 1
curl -H "Authorization: Bearer $TOKEN" http://localhost/api/recommendations/similar/1

# View all likes
curl -H "Authorization: Bearer $TOKEN" http://localhost/api/likes
```

That's it! The system is ready to test. 🚀
