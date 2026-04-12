<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RecommendationController;
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

// Telegram webhook (public, no auth required)
Route::post('/telegram/webhook', [TelegramController::class, 'webhook']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', function (Request $request) {
        return $request->user();
    });

    // Content-Based Filtering & Recommendations
    Route::prefix('recommendations')->group(function () {
        Route::get('/', [RecommendationController::class, 'getRecommendations']);
        Route::get('/similar/{event}', [RecommendationController::class, 'getSimilarEvents']);
    });

    // Event Likes (for increasing recommendation accuracy)
    Route::prefix('events')->group(function () {
        Route::post('/{event}/like', [RecommendationController::class, 'likeEvent']);
        Route::post('/{event}/unlike', [RecommendationController::class, 'unlikeEvent']);
        Route::get('/{event}/like-status', [RecommendationController::class, 'getEventLikeStatus']);
    });

    Route::get('likes', [RecommendationController::class, 'getUserLikes']);

    // Telegram Bot Integration
    Route::prefix('telegram')->group(function () {
        Route::post('/link', [TelegramController::class, 'linkAccount']);
        Route::delete('/unlink', [TelegramController::class, 'unlinkAccount']);
        Route::get('/preferences', [TelegramController::class, 'getPreferences']);
        Route::put('/preferences', [TelegramController::class, 'updatePreferences']);
        Route::get('/events/thisweek', [TelegramController::class, 'getThisWeekEvents']);
    });
});