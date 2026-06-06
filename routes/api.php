<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\ClubController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\TelegramController;
use App\Http\Controllers\Api\ClubFollowController;
use App\Http\Controllers\Api\ClubNotificationController;
use App\Models\Event;

// Health check endpoint (no auth required)
Route::get('/health', function () {
    $requiredPaths = [
        'storage' => storage_path(),
        'framework_cache' => storage_path('framework/cache/data'),
        'framework_sessions' => storage_path('framework/sessions'),
        'framework_views' => storage_path('framework/views'),
        'bootstrap_cache' => base_path('bootstrap/cache'),
        'logs' => storage_path('logs'),
    ];

    $storageIssues = [];

    foreach ($requiredPaths as $name => $path) {
        if (! is_dir($path)) {
            $storageIssues[] = $name . ' directory is missing';
            continue;
        }

        if (! is_writable($path)) {
            $storageIssues[] = $name . ' directory is not writable';
        }
    }

    $status = empty($storageIssues) ? 'ok' : 'degraded';

    return response()->json([
        'status' => $status,
        'message' => 'API is running',
        'storage' => [
            'status' => $status,
            'issues' => $storageIssues,
        ],
        'timestamp' => now()
    ], $status === 'ok' ? 200 : 503);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public Event Endpoints (no auth required)
Route::prefix('events')->group(function () {
    Route::get('/', [EventController::class, 'index']);
    Route::get('/search', [EventController::class, 'search']);
    Route::get('/{event}', [EventController::class, 'show']);
    Route::get('/{event}/join-status', [EventController::class, 'getJoinStatus']);
    Route::get('/{event}/like-status', [EventController::class, 'getLikeStatus']);
});

// Public Clubs Endpoints (no auth required)
Route::prefix('clubs')->group(function () {
    Route::get('/', [ClubController::class, 'index']);
    Route::get('/search', [ClubController::class, 'search']);
    Route::get('/{club}', [ClubController::class, 'show']);
    Route::get('/{club}/events', [ClubController::class, 'getEvents']);
});

// Telegram webhook (public, no auth required)
Route::post('/telegram/webhook', [TelegramController::class, 'webhook']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', function (Request $request) {
        $user = $request->user();
        return response()->json($user);
    });

    // Profile Update
    Route::post('/profile/update', [AuthController::class, 'updateProfile']);

    // Content-Based Filtering & Recommendations
    Route::prefix('recommendations')->group(function () {
        Route::get('/', [RecommendationController::class, 'getRecommendations']);
        Route::get('/similar/{event}', [RecommendationController::class, 'getSimilarEvents']);
    });

    // Event Likes (for increasing recommendation accuracy)
    Route::prefix('events')->group(function () {
        // Required Like API
        Route::post('/{event}/like', [EventController::class, 'like']);
        Route::delete('/{event}/like', [EventController::class, 'unlike']);

        // Backward compatibility for older mobile clients
        Route::post('/{event}/unlike', [EventController::class, 'unlike']);
        Route::get('/{event}/like-status', [EventController::class, 'getLikeStatus']);
        
        // Event Registration/Join
        Route::post('/{event}/join', [EventController::class, 'join']);
        Route::post('/{event}/leave', [EventController::class, 'leave']);
    });

    Route::post('/clubs/{club}/follow', [ClubFollowController::class, 'follow']);
    Route::delete('/clubs/{club}/follow', [ClubFollowController::class, 'unfollow']);
    Route::post('/clubs/{club}/unfollow', [ClubFollowController::class, 'unfollow']);

    Route::get('likes', [RecommendationController::class, 'getUserLikes']);
    Route::get('/users/{user}/liked-events', [EventController::class, 'getUserLikedEvents']);

    Route::prefix('user')->group(function () {
        Route::get('/followed-clubs', [ClubFollowController::class, 'followedClubs']);
        Route::get('/followed-events', [ClubFollowController::class, 'followedEvents']);
    });

    Route::prefix('notifications')->group(function () {
        Route::get('/', [ClubNotificationController::class, 'index']);
        Route::post('/{notification}/read', [ClubNotificationController::class, 'markAsRead']);
    });

    // User Data Endpoints
    Route::get('/me/registrations', [AuthController::class, 'getUserRegistrations']);
    Route::get('/me/orders', [AuthController::class, 'getUserOrders']);

    // Cart Endpoints
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::put('/cart/update/{cartItemId}', [CartController::class, 'update']);
    Route::delete('/cart/remove/{cartItemId}', [CartController::class, 'remove']);
    Route::post('/cart/checkout', [CartController::class, 'checkout']);

    // Attendance Endpoints
    Route::post('/attendance/scan', [AttendanceController::class, 'scan']);

    // Telegram Bot Integration
    Route::prefix('telegram')->group(function () {
        Route::post('/link', [TelegramController::class, 'linkAccount']);
        Route::delete('/unlink', [TelegramController::class, 'unlinkAccount']);
        Route::get('/preferences', [TelegramController::class, 'getPreferences']);
        Route::put('/preferences', [TelegramController::class, 'updatePreferences']);
        Route::get('/events/thisweek', [TelegramController::class, 'getThisWeekEvents']);
    });

    // Super Admin: User inspection endpoint (returns profile + activity)
    Route::middleware('super_admin')->group(function () {
        Route::get('/admin/users/{id}', [\App\Http\Controllers\Api\AdminUserController::class, 'show']);
    });
});
