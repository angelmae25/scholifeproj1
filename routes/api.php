<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\PointController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('register',      [AuthController::class, 'register']);
Route::post('login',         [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout',    [AuthController::class, 'logout']);
    Route::get('me',         [AuthController::class, 'me']);
    Route::put('profile',    [AuthController::class, 'updateProfile']);

    // Announcements
    Route::get('announcements',       [AnnouncementController::class, 'index']);
    Route::get('announcements/{id}',  [AnnouncementController::class, 'show']);

    // Events
    Route::get('events',              [EventController::class, 'index']);
    Route::get('events/{id}',         [EventController::class, 'show']);
    Route::post('events/{id}/rsvp',   [EventController::class, 'rsvp']);

    // Organizations
    Route::get('organizations',       [OrganizationController::class, 'index']);
    Route::get('organizations/{id}',  [OrganizationController::class, 'show']);

    // Points
    Route::get('points/leaderboard',  [PointController::class, 'leaderboard']);
    Route::get('points/my',           [PointController::class, 'myPoints']);

    // Dashboard
    Route::get('dashboard',           [UserController::class, 'dashboard']);

    // Device token for push notifications
    Route::post('device-token',       [AuthController::class, 'saveDeviceToken']);
});
