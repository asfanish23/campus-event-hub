<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\AdminRegistrationController;
use App\Http\Controllers\Web\StudentRegistrationController;
use App\Http\Controllers\Web\StudentDashboardController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ClubProfileController;
use App\Http\Controllers\Web\EventController;
use App\Http\Controllers\Web\AttendanceController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\OrderController;
use App\Http\Controllers\Web\SuperAdminController;
use App\Http\Controllers\Web\InstagramController;
use App\Http\Controllers\Web\ClubInstagramController;
use App\Http\Controllers\Web\PaymentController;
use App\Http\Controllers\Web\PaymentTestController;
use App\Http\Controllers\Web\StudentProfileController;
use App\Http\Controllers\Web\ShoppingController;
use App\Http\Controllers\Web\InstagramOAuthController;
use App\Http\Controllers\Web\AiGeneratorController;

Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthWebController::class, 'login'])->name('login.submit');

Route::get('/forgot-password', [AuthWebController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthWebController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthWebController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthWebController::class, 'resetPassword'])->name('password.store');

Route::get('/register/admin', [AdminRegistrationController::class, 'showRegister'])->name('admin-register');
Route::post('/register/admin', [AdminRegistrationController::class, 'register'])->name('admin-register.submit');

Route::get('/register/student', [StudentRegistrationController::class, 'showRegister'])->name('student-register');
Route::post('/register/student', [StudentRegistrationController::class, 'register'])->name('student-register.submit');

Route::middleware('auth')->group(function () {
    Route::get('/student/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard')->middleware('student');
    Route::get('/student/calendar', [StudentDashboardController::class, 'calendar'])->name('student.calendar')->middleware('student');
    Route::get('/student/archive', [StudentDashboardController::class, 'archive'])->name('student.archive')->middleware('student');
    Route::get('/student/clubs', [StudentDashboardController::class, 'clubs'])->name('student.clubs')->middleware('student');
    Route::get('/student/shop', [ShoppingController::class, 'index'])->name('student.shop')->middleware('student');
    Route::get('/student/shop/{product}', [ShoppingController::class, 'show'])->name('student.shop.show')->middleware('student');
    Route::get('/student/club/{club}', [StudentDashboardController::class, 'showClub'])->name('student.club.show')->middleware('student');
    Route::get('/student/event/{event}', [StudentDashboardController::class, 'showEvent'])->name('student.event.show')->middleware('student');
    Route::post('/student/event/{event}/register', [StudentDashboardController::class, 'registerEvent'])->name('student.event.register')->middleware('student');
    Route::post('/student/event/{event}/cancel-registration', [StudentDashboardController::class, 'cancelRegistration'])->name('student.event.cancel')->middleware('student');
    Route::post('/student/event/{event}/like', [StudentDashboardController::class, 'likeEvent'])->name('student.event.like')->middleware('student');
    Route::post('/student/event/{event}/unlike', [StudentDashboardController::class, 'unlikeEvent'])->name('student.event.unlike')->middleware('student');
    
    // Student Profile routes
    Route::prefix('student/profile')->middleware('student')->group(function () {
        Route::get('/', [StudentProfileController::class, 'show'])->name('student.profile.show');
        Route::get('/edit', [StudentProfileController::class, 'edit'])->name('student.profile.edit');
        Route::post('/update', [StudentProfileController::class, 'update'])->name('student.profile.update');
        Route::post('/upload-photo', [StudentProfileController::class, 'uploadPhoto'])->name('student.profile.upload-photo');
        Route::get('/registrations', [StudentProfileController::class, 'registrations'])->name('student.profile.registrations');
        Route::get('/cart', [StudentProfileController::class, 'cart'])->name('student.profile.cart');
        Route::get('/orders', [StudentProfileController::class, 'orders'])->name('student.profile.orders');
        Route::get('/payments', [StudentProfileController::class, 'payments'])->name('student.profile.payments');
    });
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Payment routes
    Route::post('/payment/pay', [PaymentController::class, 'createBill'])->name('payment.pay')->middleware('auth');
    Route::post('/payment/checkout-multiple', [PaymentController::class, 'checkoutMultiple'])->name('payment.checkout.multiple')->middleware('auth');
    Route::get('/payment/return', [PaymentController::class, 'return'])->name('payment.return')->middleware('auth');
    Route::post('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback'); // No auth - called by ToyyibPay
    
    // Payment test routes (for sandbox/development testing only)
    Route::get('/payment/test/success/{payment_id}', [PaymentTestController::class, 'simulateSuccess'])->name('payment.test.success')->middleware('auth');
    Route::get('/payment/test/failed/{payment_id}', [PaymentTestController::class, 'simulateFailure'])->name('payment.test.failure')->middleware('auth');

    Route::get('/club-profile', [ClubProfileController::class, 'show'])->name('club-profile.show');
    Route::get('/club-profile/edit', [ClubProfileController::class, 'edit'])->name('club-profile.edit');
    Route::post('/club-profile/update', [ClubProfileController::class, 'update'])->name('club-profile.update');

    Route::resource('event', EventController::class);
    Route::get('event/{event}/attendance', [EventController::class, 'attendance'])->name('event.attendance');
    Route::get('event/{event}/reviews', [EventController::class, 'reviews'])->name('event.reviews');

    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::put('attendance/{attendance}', [AttendanceController::class, 'update'])->name('attendance.update');

    Route::delete('event-media/{eventMedia}', [EventController::class, 'deleteMedia'])->name('event-media.destroy');

    Route::resource('merchandise', ProductController::class);
    Route::resource('orders', OrderController::class)->only(['index', 'update']);

    // Instagram management routes
    Route::get('/instagram', [InstagramController::class, 'index'])->name('instagram.index');
    Route::post('/instagram/post-event/{event}', [InstagramController::class, 'postEvent'])->name('instagram.post-event');
    Route::post('/instagram/schedule-event/{event}', [InstagramController::class, 'scheduleEvent'])->name('instagram.schedule-event');
    Route::post('/instagram/cancel-scheduled/{event}', [InstagramController::class, 'cancelScheduledPost'])->name('instagram.cancel-scheduled');
    Route::post('/instagram/repost-now/{event}', [InstagramController::class, 'repostNow'])->name('instagram.repost-now');
    Route::post('/instagram/schedule-repost/{event}', [InstagramController::class, 'scheduleRepost'])->name('instagram.schedule-repost');
    Route::post('/instagram/cancel-repost-schedule/{event}', [InstagramController::class, 'cancelRepostSchedule'])->name('instagram.cancel-repost-schedule');
    Route::get('/instagram/settings', [InstagramController::class, 'settings'])->name('instagram.settings');
    Route::get('/instagram/test', [InstagramController::class, 'testApi'])->name('instagram.test');

    // Club Instagram credential management routes
    Route::post('/club-instagram/store-credentials', [ClubInstagramController::class, 'storeCredentials'])->name('club-instagram.store-credentials');
    Route::post('/club-instagram/disconnect', [ClubInstagramController::class, 'disconnect'])->name('club-instagram.disconnect');
    Route::get('/club-instagram/status', [ClubInstagramController::class, 'getStatus'])->name('club-instagram.status');

    // Instagram OAuth routes (for easier connection)
    Route::get('/instagram/oauth/redirect/{clubId}', [InstagramOAuthController::class, 'redirectToInstagram'])->name('instagram.oauth.redirect');
    Route::get('/instagram/oauth/callback', [InstagramOAuthController::class, 'handleCallback'])->name('instagram.oauth.callback');
    Route::post('/instagram/oauth/fetch-account', [InstagramOAuthController::class, 'fetchAccountFromToken'])->name('instagram.oauth.fetch-account');

    Route::prefix('super-admin')->name('super-admin.')->middleware(['auth', 'super_admin'])->group(function () {
        Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/manage-events', [SuperAdminController::class, 'manageEvents'])->name('manage-events');
        Route::get('/events/create', [SuperAdminController::class, 'createEvent'])->name('events.create');
        Route::post('/events', [SuperAdminController::class, 'storeEvent'])->name('events.store');
        Route::get('/events/{id}', [SuperAdminController::class, 'showEvent'])->name('events.show');
        Route::get('/events/{id}/edit', [SuperAdminController::class, 'editEvent'])->name('events.edit');
        Route::put('/events/{id}', [SuperAdminController::class, 'updateEvent'])->name('events.update');
        Route::post('/events/{id}/toggle-qr', [SuperAdminController::class, 'toggleQRStatus'])->name('events.toggle-qr');
        Route::delete('/events/{id}', [SuperAdminController::class, 'deleteEvent'])->name('events.delete');
        Route::get('/manage-clubs', [SuperAdminController::class, 'manageClubs'])->name('manage-clubs');
        Route::get('/clubs/create', [SuperAdminController::class, 'createClub'])->name('clubs.create');
        Route::post('/clubs', [SuperAdminController::class, 'storeClub'])->name('clubs.store');
        Route::get('/clubs/{id}/edit', [SuperAdminController::class, 'editClub'])->name('clubs.edit');
        Route::get('/clubs/{id}', [SuperAdminController::class, 'showClub'])->name('clubs.show');
        Route::put('/clubs/{id}', [SuperAdminController::class, 'updateClub'])->name('clubs.update');
        Route::delete('/clubs/{id}', [SuperAdminController::class, 'deleteClub'])->name('clubs.delete');
        Route::get('/manage-users', [SuperAdminController::class, 'manageUsers'])->name('manage-users');
        Route::post('/users/{user}/approve', [SuperAdminController::class, 'approveAdmin'])->name('approve-admin');
        Route::post('/users/{user}/reject', [SuperAdminController::class, 'rejectAdmin'])->name('reject-admin');
        Route::put('/users/{user}/update-role', [SuperAdminController::class, 'updateUserRole'])->name('update-user-role');
        Route::delete('/users/{user}', [SuperAdminController::class, 'deleteUser'])->name('delete-user');
        Route::get('/manage-reviews', [SuperAdminController::class, 'manageReviews'])->name('manage-reviews');
        Route::get('/system-settings', [SuperAdminController::class, 'systemSettings'])->name('system-settings');
    });

    // AI Generator routes
    Route::post('/ai/generate-description', [AiGeneratorController::class, 'generateDescription'])->name('ai.generate-description');
    Route::post('/ai/tweak-description', [AiGeneratorController::class, 'tweakDescription'])->name('ai.tweak-description');

    Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');
});

