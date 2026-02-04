# Content-Based Filtering - Quick Reference

## What's New?

✅ **Content-Based Filtering Service** - Intelligent event recommendation engine
✅ **Like Button System** - Users can like events to improve recommendations
✅ **Personalized Recommendations** - API endpoints for user-specific event suggestions
✅ **Similar Events** - Find events similar to ones users are interested in

## Quick Start

### For Frontend Developers

#### 1. Like an Event
```javascript
// User clicks like button on event
POST /api/events/{eventId}/like
```

#### 2. Get Recommendations
```javascript
// Load recommended events on dashboard
GET /api/recommendations?limit=10
```

#### 3. Get Similar Events
```javascript
// Show related events on event detail page
GET /api/recommendations/similar/{eventId}
```

## API Endpoints Summary

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/recommendations` | Get personalized recommendations |
| GET | `/api/recommendations/similar/{id}` | Get similar events |
| POST | `/api/events/{id}/like` | Like an event |
| POST | `/api/events/{id}/unlike` | Unlike an event |
| GET | `/api/events/{id}/like-status` | Check like status |
| GET | `/api/likes` | Get user's liked events |

## How It Works

### Recommendation Algorithm

```
Step 1: Analyze User's Liked Events
        ↓
Step 2: Build User Profile (categories, clubs, locations)
        ↓
Step 3: Score Each Available Event
        - Profile Relevance (60%): How well matches user preferences
        - Content Similarity (40%): How similar to liked events
        ↓
Step 4: Rank by Combined Score
        ↓
Step 5: Return Top N Events
```

### Scoring Formula

```
Final Score = (0.6 × Profile Relevance) + (0.4 × Similarity Score)
```

## Feature Importance

Events are compared using these features (in order of importance):

1. **Category** (35%) - Sports, Tech, Social, etc.
2. **Club** (25%) - Which club is hosting
3. **Location** (15%) - Where the event is
4. **Status** (10%) - Published, draft, etc.
5. **Temporal** (15%) - Upcoming vs past events

## Example Flow

### User Journey

```
1. User logs in
   ↓
2. Sees 3 recommended sports events (no likes yet)
   ↓
3. Clicks "Like" on Basketball Championship
   ↓
4. System updates user profile with sports interest
   ↓
5. Gets better recommendations with more sports events
   ↓
6. Clicks "Like" on Football Tournament
   ↓
7. Gets even more relevant sports recommendations
```

## Implementation Checklist

- [x] Content-Based Filtering Service created
- [x] Like/Unlike endpoints implemented
- [x] Recommendation endpoints created
- [x] Similar events endpoint added
- [x] User likes endpoint added
- [x] Event helper methods added
- [x] API routes configured
- [x] Documentation complete

## Database Tables Used

- **event_likes** - Stores user likes (already existed)
- **events** - Event details
- **users** - User information

No new tables needed! Uses existing infrastructure.

## Code Files Added/Modified

### New Files
- `app/Services/ContentBasedFilteringService.php` - Core algorithm
- `app/Http/Controllers/Api/RecommendationController.php` - API endpoints
- `CONTENT_BASED_FILTERING_GUIDE.md` - Full documentation

### Modified Files
- `routes/api.php` - Added new routes
- `app/Models/Event.php` - Added helper methods
- `app/Models/User.php` - Already has like relationships

## Testing the API

### Using cURL

```bash
# Get recommendations
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost/api/recommendations?limit=5

# Like an event
curl -X POST \
  -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost/api/events/1/like

# Get similar events
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost/api/recommendations/similar/1

# Check like status
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost/api/events/1/like-status
```

### Using Postman

1. Create collection "CampusEventHub"
2. Get auth token from login endpoint
3. Set Authorization header: `Bearer {token}`
4. Create requests for each endpoint above

## Performance Tips

1. **Frontend Caching** - Cache recommendations for 5 minutes
2. **Lazy Loading** - Load recommendations when user scrolls
3. **Pagination** - Use `?limit=5` for quick loads
4. **Profile Building** - More likes = Better recommendations

## Cold Start Handling

For new users with no likes:
- System returns popular events across all categories
- As user likes events, recommendations become personalized
- After 3-5 likes, recommendations are meaningful

## Next Steps

1. **Add to UI** - Integrate like button in frontend
2. **Display Recommendations** - Show on dashboard/home
3. **Track Interactions** - Monitor recommendation effectiveness
4. **Iterate** - Adjust weights based on user feedback

## Questions?

See full documentation in `CONTENT_BASED_FILTERING_GUIDE.md`
