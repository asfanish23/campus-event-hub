<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\ClubController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\TelegramController;
use App\Models\Event;

// Health check endpoint (no auth required)
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API is running',
        'timestamp' => now()
    ]);
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

    Route::get('likes', [RecommendationController::class, 'getUserLikes']);
    Route::get('/users/{user}/liked-events', [EventController::class, 'getUserLikedEvents']);

    // User Data Endpoints
    Route::get('/me/registrations', [AuthController::class, 'getUserRegistrations']);
    Route::get('/me/orders', [AuthController::class, 'getUserOrders']);

    // Cart Endpoints
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'add']);

    // Telegram Bot Integration
    Route::prefix('telegram')->group(function () {
        Route::post('/link', [TelegramController::class, 'linkAccount']);
        Route::delete('/unlink', [TelegramController::class, 'unlinkAccount']);
        Route::get('/preferences', [TelegramController::class, 'getPreferences']);
        Route::put('/preferences', [TelegramController::class, 'updatePreferences']);
        Route::get('/events/thisweek', [TelegramController::class, 'getThisWeekEvents']);
    });
});