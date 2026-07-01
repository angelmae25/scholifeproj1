<?php
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\AdminAccountController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AcademicNoticeController;
use App\Http\Controllers\PointsController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\OrganizationEvaluationController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('admin.login'));

Route::get('evaluations/organizations/{organization}', [OrganizationEvaluationController::class, 'show'])
    ->name('evaluations.organizations.show');
Route::post('evaluations/organizations/{organization}', [OrganizationEvaluationController::class, 'store'])
    ->name('evaluations.organizations.store');
Route::get('evaluations/organizations/{organization}/thank-you', [OrganizationEvaluationController::class, 'thankYou'])
    ->name('evaluations.organizations.thank-you');

Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('guest:admin')->group(function () {
        Route::get('login',  [AdminLoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AdminLoginController::class, 'login'])->middleware('throttle:5,1');
    });

    Route::post('logout', [AdminLoginController::class, 'logout'])
        ->name('logout')->middleware('auth:admin');

    Route::middleware('auth:admin')->group(function () {
        Route::get('admin-accounts/{admin}', [AdminAccountController::class, 'show'])->name('admin-accounts.show');
        Route::patch('admin-accounts/{admin}', [AdminAccountController::class, 'update'])->name('admin-accounts.update');
        Route::middleware('admin.permission:dashboard')->group(function () {
            Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        });

        Route::middleware('admin.permission:analytics')->group(function () {
            Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics');
        });

        Route::middleware('admin.permission:logs')->group(function () {
            Route::get('logs', [LogController::class, 'index'])->name('logs');
        });

        Route::middleware('admin.permission:users')->group(function () {
            Route::get('users', [UserController::class, 'index'])->name('users');
        });

        Route::middleware('admin.permission:announcements')->group(function () {
            Route::get('announcements', [AnnouncementController::class, 'index'])->name('announcements');
            Route::post('announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
            Route::get('announcements/{announcement}', [AnnouncementController::class, 'show'])->name('announcements.show');
            Route::delete('announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
        });

        Route::middleware('admin.permission:events')->group(function () {
            Route::get('events', [EventController::class, 'index'])->name('events');
            Route::post('events', [EventController::class, 'store'])->name('events.store');
            Route::get('events/{event}', [EventController::class, 'show'])->name('events.show');
            Route::delete('events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
        });

        Route::middleware('admin.permission:organizations')->group(function () {
            Route::get('organizations', [OrganizationController::class, 'index'])->name('organizations');
            Route::post('organizations', [OrganizationController::class, 'store'])->name('organizations.store');
            Route::get('organizations/{organization}', [OrganizationController::class, 'show'])->name('organizations.show');
            Route::patch('organizations/{organization}', [OrganizationController::class, 'update'])->name('organizations.update');
            Route::delete('organizations/{organization}', [OrganizationController::class, 'destroy'])->name('organizations.destroy');
            Route::patch('organizations/{organization}/status', [OrganizationController::class, 'updateStatus'])->name('organizations.status');
            Route::post('organizations/{organization}/assign', [OrganizationController::class, 'assign'])->name('organizations.assign');
            Route::delete('organizations/{organization}/unassign/{member}', [OrganizationController::class, 'unassign'])->name('organizations.unassign');
        });

        Route::middleware('admin.permission:admin-accounts')->group(function () {
            Route::get('admin-accounts', [AdminAccountController::class, 'index'])->name('admin-accounts');
            Route::post('admin-accounts', [AdminAccountController::class, 'store'])->name('admin-accounts.store');
            Route::post('admin-accounts/{admin}/toggle', [AdminAccountController::class, 'toggle'])->name('admin-accounts.toggle');
        });

        Route::middleware('admin.permission:reports')->group(function () {
            Route::get('reports', [ReportController::class, 'index'])->name('reports');
            Route::get('reports/{report}', [ReportController::class, 'show'])->name('reports.show');
            Route::get('reports/{report}/download', [ReportController::class, 'download'])->name('reports.download');
        });

        Route::middleware('admin.permission:academic-notices')->group(function () {
            Route::get('academic-notices', [AcademicNoticeController::class, 'index'])->name('academic-notices');
            Route::post('academic-notices', [AcademicNoticeController::class, 'store'])->name('academic-notices.store');
            Route::get('academic-notices/{academicNotice}', [AcademicNoticeController::class, 'show'])->name('academic-notices.show');
            Route::delete('academic-notices/{academicNotice}', [AcademicNoticeController::class, 'destroy'])->name('academic-notices.destroy');
            Route::patch('academic-notices/{academicNotice}/approve', [AcademicNoticeController::class, 'approve'])->name('academic-notices.approve');
        });

        Route::middleware('admin.permission:points')->group(function () {
            Route::get('points', [PointsController::class, 'index'])->name('points');
            Route::get('points/leaderboard', [PointsController::class, 'leaderboard'])->name('points.leaderboard');
            Route::post('points/rules', [PointsController::class, 'storeRule'])->name('points.rules.store');
        });
    });
});





