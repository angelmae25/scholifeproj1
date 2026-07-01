<?php
use App\Http\Controllers\Api\MobileNewsController;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileEventController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MobilePreLovedItemController;
use App\Http\Controllers\Api\MobilePreLovedMessageController;
use App\Http\Controllers\Api\MobileLostFoundController;
use App\Http\Controllers\Api\MobileOrganizationController;
use App\Http\Controllers\Api\MobileLeaderboardController;
use App\Http\Controllers\Api\MobileReportController;
use App\Http\Controllers\Api\MobileProfileController;
use App\Http\Controllers\Api\MobileOrganizationEvaluationController;

Route::post('/mobile/register', [MobileAuthController::class, 'register']);
Route::post('/mobile/login', [MobileAuthController::class, 'login']);
Route::get('/mobile/news', [MobileNewsController::class, 'index']);
Route::get('/mobile/events', [MobileEventController::class, 'index']);
Route::get('/mobile/pre-loved-items', [MobilePreLovedItemController::class, 'index']);
Route::get('/mobile/organizations', [MobileOrganizationController::class, 'index']);
Route::get('/mobile/organizations/{organization}', [MobileOrganizationController::class, 'show']);
Route::get('/mobile/organizations/{organization}/evaluation', [MobileOrganizationEvaluationController::class, 'show']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/mobile/pre-loved-items', [MobilePreLovedItemController::class, 'store']);
    Route::get('/mobile/pre-loved-messages', [MobilePreLovedMessageController::class, 'conversations']);
    Route::get('/mobile/pre-loved-items/{item}/messages', [MobilePreLovedMessageController::class, 'index']);
    Route::post('/mobile/pre-loved-items/{item}/messages', [MobilePreLovedMessageController::class, 'store']);
});
Route::get('/mobile/lost-found-items', [MobileLostFoundController::class, 'index']);
Route::get('/mobile/leaderboard', [MobileLeaderboardController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/mobile/lost-found-items/{item}/claim', [MobileLostFoundController::class, 'claim']);
    Route::post('/mobile/lost-found-items', [MobileLostFoundController::class, 'store']);
    Route::post('/mobile/news', [MobileNewsController::class, 'store']);
    Route::post('/mobile/news/{announcement}/view', [MobileNewsController::class, 'view']);
    Route::post('/mobile/events', [MobileEventController::class, 'store']);
    Route::post('/mobile/events/{event}/view', [MobileEventController::class, 'view']);
    Route::get('/mobile/students', [MobileOrganizationController::class, 'students']);
    Route::post('/mobile/organizations/{organization}/assign-role', [MobileOrganizationController::class, 'assignRole']);
    Route::get('/mobile/organization-permissions', [MobileOrganizationController::class, 'permissions']);
    Route::post('/mobile/organizations/{organization}/evaluation', [MobileOrganizationEvaluationController::class, 'store']);
    Route::post('/mobile/reports', [MobileReportController::class, 'store']);
    Route::get('/mobile/profile', [MobileProfileController::class, 'show']);
    Route::post('/mobile/profile', [MobileProfileController::class, 'update']);
});
