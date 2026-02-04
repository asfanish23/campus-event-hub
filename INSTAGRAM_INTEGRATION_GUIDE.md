# Multi-Club Instagram Integration System

## Overview

This document describes the complete multi-club Instagram automatic posting system implemented in Campus Event Hub. The system allows each club to independently manage their own Instagram business account and automatically post event posters when new events are created.

## Architecture

### Key Components

#### 1. **Database Layer**
- **Table**: `instagram_accounts`
- **Purpose**: Store per-club Instagram credentials securely
- **Fields**:
  - `id` - Primary key
  - `club_id` - Foreign key to clubs table (unique, one account per club)
  - `instagram_username` - Display name of the Instagram business account
  - `instagram_business_id` - Instagram's numeric business account ID
  - `access_token` - Encrypted access token (stored as encrypted text)
  - `is_active` - Boolean flag to enable/disable without deleting
  - `token_expires_at` - Timestamp for token expiration tracking
  - `last_post_at` - Timestamp of the most recent post
  - `created_at`, `updated_at` - Timestamps

#### 2. **Model Layer**
- **InstagramAccount Model** (`app/Models/InstagramAccount.php`)
  - Automatically encrypts/decrypts the `access_token` field
  - Methods:
    - `isTokenValid()` - Checks if token is active and not expired
    - `getDecryptedToken()` - Retrieves and returns the decrypted token
  - Relationships:
    - `belongsTo(Club::class)` - Links to parent club

- **Club Model** (updated)
  - Added method: `instagramAccount()` - HasOne relationship

#### 3. **Service Layer**

##### InstagramService (`app/Services/InstagramService.php`)
Core Instagram Graph API integration with support for custom credentials.

**Methods**:
- `postImage($imageUrl, $caption)` - Legacy method using global config credentials
- `postImageWithCustomCredentials($imageUrl, $caption, $token, $businessId)` - **NEW**: Posts using club-specific credentials
- `createMediaContainer($imageUrl, $caption)` - Creates media container (Step 1)
- `createMediaContainerCustom(...)` - Step 1 with custom credentials
- `publishMedia($mediaContainerId)` - Publishes media (Step 2)
- `publishMediaCustom(...)` - Step 2 with custom credentials

**Key Features**:
- Instagram API v18.0 (Graph API)
- Two-step posting process (container creation → publication)
- 3-second delay before publishing to allow Instagram processing
- Comprehensive error logging
- Automatic HTTP → HTTPS URL conversion
- Ngrok URL support for localhost environments

##### ImgBBService (`app/Services/ImgBBService.php`)
Free image hosting service for public image URLs (required since Instagram cannot access localhost).

**Method**:
- `uploadImage($imagePath, $name)` - Uploads image to ImgBB and returns public HTTPS URL

**Features**:
- Base64 image encoding
- API key passed as query parameter
- Returns shareable public image URLs

##### ClubInstagramService (`app/Services/ClubInstagramService.php`)
**NEW**: Orchestrates per-club Instagram posting with credential validation.

**Method**:
- `postEventToClubInstagram($club, $localImagePath, $caption, $eventId)` - Main orchestration method

**Process**:
1. Validates club has Instagram account configured
2. Verifies access token is valid and not expired
3. Uploads image to ImgBB for public hosting
4. Calls InstagramService with club-specific credentials
5. Updates `last_post_at` timestamp on success
6. Returns comprehensive response with success/error status

#### 4. **Controller Layer**

##### EventController (`app/Http/Controllers/Web/EventController.php`)
**Updated `store()` method**:
- When an event is created with an event image:
  1. Gets the club associated with the current user
  2. Creates a caption from event details
  3. Calls `ClubInstagramService::postEventToClubInstagram()`
  4. Logs success/failure
  5. Event creation continues regardless of Instagram posting result

##### ClubInstagramController (`app/Http/Controllers/Web/ClubInstagramController.php`)
**NEW**: Manages Instagram credential configuration for clubs.

**Methods**:
- `storeCredentials(Request $request)` - Saves/updates Instagram account credentials
- `disconnect(Request $request)` - Disables Instagram posting for the club
- `getStatus()` - Returns JSON status of club's Instagram account connection

#### 5. **View Layer**

##### Club Profile Edit View (`resources/views/club-profile/edit.blade.php`)
**NEW Section**: "Instagram Auto-Posting Setup"
- Displays connection status (Connected/Not Connected)
- Shows connected username and last post time
- For unconnected clubs: Form to input credentials
  - Instagram Username input
  - Instagram Business Account ID input
  - Access Token textarea
  - Help text with instructions on getting credentials
- For connected clubs: Disconnect button

##### Club Profile Show View (`resources/views/club-profile/show.blade.php`)
**NEW Section**: "Instagram Auto-Posting"
- Shows current connection status
- Displays connected account details if applicable
- Link to configure/edit credentials
- Information banner explaining automatic posting

#### 6. **Routes**

**New routes** in `routes/web.php`:
```php
Route::post('/club-instagram/store-credentials', 'storeCredentials')->name('club-instagram.store-credentials');
Route::post('/club-instagram/disconnect', 'disconnect')->name('club-instagram.disconnect');
Route::get('/club-instagram/status', 'getStatus')->name('club-instagram.status');
```

## Data Flow

### Event Creation Flow (Automatic Posting)

```
User creates event with image
    ↓
EventController@store validates input
    ↓
Event + event_image saved to database
    ↓
Retrieve club from Auth::user()
    ↓
ClubInstagramService::postEventToClubInstagram() called
    ├─ Verify club has Instagram account
    ├─ Verify token is valid
    ├─ ImgBBService uploads image → public HTTPS URL
    ├─ InstagramService creates media container (Step 1)
    ├─ Wait 3 seconds for processing
    ├─ InstagramService publishes media (Step 2)
    └─ Update last_post_at timestamp
    ↓
Log result (success/error)
    ↓
Event creation completes
```

### Credential Management Flow

```
Club Admin visits Club Profile Edit
    ↓
Display Instagram section
    ├─ If connected: Show status + Disconnect button
    └─ If not connected: Show credential input form
    ↓
Admin fills in:
  - Instagram Username
  - Instagram Business Account ID
  - Access Token
    ↓
Form submits to ClubInstagramController@storeCredentials
    ↓
FindOrCreate InstagramAccount record for club
    ↓
Set fields + mark is_active = true
    ↓
Access token automatically encrypted on save
    ↓
Redirect back with success message
```

## Security Features

### Token Encryption
- All access tokens are automatically encrypted using Laravel's `Crypt` facade
- Encryption happens on model save
- Decryption happens on-demand via `getDecryptedToken()` method
- Database stores encrypted tokens as `longText`

### Data Validation
- Instagram username validation (required, string, max 255)
- Business ID validation (required, string, max 255)
- Token validation (required, string)
- Token expiration checking before posting

### Access Control
- Club-specific Instagram accounts tied to `club_id`
- Only club admin can manage their own club's credentials
- Middleware ensures authenticated users only
- Relationship constraints prevent access to other clubs' accounts

## Getting Instagram Credentials

### Step-by-Step Setup

1. **Create Meta Developer App**
   - Go to https://developers.facebook.com
   - Click "Create App"
   - Select "Business" app type
   - Name your app and follow setup wizard

2. **Add Instagram Graph API**
   - In your app dashboard
   - Click "Add Product"
   - Search for "Instagram Graph API"
   - Click "Set Up"

3. **Get Business Account ID**
   - Go to Settings → Basic
   - Find your Instagram Business Account ID (numeric)
   - Copy and save this value

4. **Generate Access Token**
   - Go to Tools & Support section
   - Select "Graph API Explorer"
   - Choose your app from dropdown
   - Generate access token with permissions:
     - `instagram_basic`
     - `instagram_content_publish`
   - Token is long alphanumeric string
   - Copy and save

5. **Add to Campus Event Hub**
   - Go to Club Profile → Edit
   - Scroll to "Instagram Auto-Posting Setup"
   - Click "Connect Instagram Account"
   - Paste the three values:
     - Username (e.g., "my_club_name")
     - Business Account ID (numeric ID)
     - Access Token (long string)
   - Click "Connect Instagram Account"

## Error Handling

### At Credential Entry
- Form validates all required fields present
- Try-catch blocks handle save errors
- User sees friendly error message
- Invalid/expired tokens caught at posting time

### At Event Posting
- `postEventToClubInstagram()` returns comprehensive response:
  ```php
  [
      'success' => bool,
      'message' => string,  // Error details if failed
      'media_id' => string  // Instagram media ID if successful
  ]
  ```
- Event creation completes even if posting fails
- Logs include full error details for debugging
- User informed via logs if posting didn't complete

## Configuration

No additional `.env` variables required for per-club system.

For **global fallback** (using old system):
```env
INSTAGRAM_USER_ID=your_user_id
INSTAGRAM_TOKEN=your_token
```

## Testing Workflow

### Test 1: Connect Instagram Account
1. Log in as club admin
2. Go to Club Profile → Edit
3. Scroll to "Instagram Auto-Posting Setup"
4. Fill in valid credentials from your Instagram business account
5. Click "Connect Instagram Account"
6. Verify success message and status shows "Connected"

### Test 2: Create Event with Auto-Posting
1. Go to Event Management → Create Event
2. Fill in event details (name, date, location, description)
3. Upload an event featured image
4. Click "Create Event"
5. Check logs: `storage/logs/laravel.log` for Instagram posting result
6. Visit your Instagram account to verify post appeared

### Test 3: Disconnect and Verify
1. Go to Club Profile → Edit
2. In "Instagram Auto-Posting Setup" section
3. Click "Disconnect" button
4. Verify status shows "Not Connected"
5. Create another event - verify image is NOT posted to Instagram
6. Check logs for warning about no account configured

## Database Migrations

Migration file: `database/migrations/2026_01_18_083707_create_instagram_accounts_table.php`

The migration has already been executed. To review:
```bash
php artisan migrate:status
```

To rollback (if needed):
```bash
php artisan migrate:rollback --step=1
```

## Dependencies

- **Laravel**: 10+ (for encryption, service container, models)
- **HTTP Client**: Built-in `Illuminate\Support\Facades\Http`
- **Encryption**: Built-in `Illuminate\Support\Facades\Crypt`
- **Instagram Graph API**: v18.0
- **ImgBB API**: v1

No additional packages required.

## Logging

All Instagram operations are logged to `storage/logs/laravel.log` with context:

```
[YYYY-MM-DD HH:MM:SS] local.INFO: Instagram post request initiated
  original_url: "https://i.ibb.co/abc123/event-1.jpg"
  caption_length: 245
  ...

[YYYY-MM-DD HH:MM:SS] local.INFO: Media published successfully
  media_id: "18423512227139289"
  ...
```

## Future Enhancements

### OAuth Implementation
The current system is ready to be upgraded to OAuth:
1. Add OAuth routes and controllers
2. Update InstagramAccount model to store refresh tokens
3. Implement token refresh logic in ClubInstagramService
4. Update credentials form to use OAuth flow instead of manual token input

### Admin Dashboard
- View all clubs and their Instagram posting status
- See posting history per club
- Monitor API failures and warnings
- Manual token refresh interface

### Analytics
- Track posts per club per month
- Monitor engagement metrics
- API rate limit tracking

## Support

### Common Issues

**"Instagram account token is invalid or expired"**
- Token may have expired
- Generate new token and update in Club Profile settings
- Check token permissions in Meta Developer console

**"Only photo or video can be accepted as media type"**
- Image format not supported by Instagram
- Use JPEG, PNG, or GIF format
- Ensure image size is reasonable (< 5MB)

**"Media ID not available"**
- Instagram needs processing time
- System includes 3-second delay
- If still failing, check logs for detailed error

**Event created but not posted to Instagram**
- Club may not have Instagram configured
- Check Club Profile → Edit → Instagram section
- Verify token is still valid in Meta Developer console
- Check logs for specific error message

## Summary

This multi-club Instagram integration provides:
✅ Secure per-club credential storage (encrypted)
✅ Automatic event poster posting
✅ Zero configuration needed from system admin
✅ Club-specific independence
✅ Comprehensive error handling and logging
✅ Easy credential management UI
✅ Ready for OAuth upgrade
✅ Tested and working with ImgBB for public image hosting
