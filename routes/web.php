<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\CropCycleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DatasetController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

// ─── Guest: Landing page ──────────────────────────────────────────────────────
Route::view('/', 'welcome')->name('home');

// ─── Authentication (Breeze) ─────────────────────────────────────────────────
require __DIR__ . '/auth.php';

// ─── Authenticated routes ────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Search Page & Autocomplete
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');

    // Chatbot Assistant
    Route::post('/chatbot/message', [ChatbotController::class, 'sendMessage'])->name('chatbot.message');

    // Datasets
    Route::delete('datasets/clear-all', [DatasetController::class, 'clearAll'])->name('datasets.clear-all');
    Route::resource('datasets', DatasetController::class);
    Route::post('datasets/{dataset}/reprocess', [DatasetController::class, 'reprocess'])
         ->name('datasets.reprocess');

    // Crop Cycles
    Route::resource('crop-cycles', CropCycleController::class);
    Route::get('crop-cycles/{cropCycle}/ndvi-chart', [CropCycleController::class, 'ndviChart'])
         ->name('crop-cycles.ndvi-chart');

    // Reports
    Route::resource('reports', ReportController::class)->except(['edit', 'update']);
    Route::get('reports/{report}/download', [ReportController::class, 'download'])
         ->name('reports.download');
    Route::get('reports/export/excel', [ReportController::class, 'exportExcel'])
         ->name('reports.export.excel');
    Route::get('reports/export/pdf', [ReportController::class, 'exportPdf'])
         ->name('reports.export.pdf');

    // Analytics / AI
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::post('/analytics/predict-yield', [AnalyticsController::class, 'predictYield'])
         ->name('analytics.predict-yield');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])
         ->name('notifications.index');
    Route::get('/notifications/poll', [NotificationController::class, 'poll'])
         ->name('notifications.poll');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])
         ->name('notifications.unread-count');
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markRead'])
         ->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])
         ->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])
         ->name('notifications.destroy');

    // Settings
    Route::get('/settings/profile',  [SettingsController::class, 'profile'])->name('settings.profile');
    Route::put('/settings/profile',  [SettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::get('/settings/security', [SettingsController::class, 'security'])->name('settings.security');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');
    Route::post('/settings/theme',   [SettingsController::class, 'toggleTheme'])->name('settings.theme.toggle');
    Route::post('/settings/locale',  [SettingsController::class, 'switchLocale'])->name('settings.locale.switch');

    // ─── Admin-only routes ────────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
             ->name('users.toggle-status');
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])
             ->name('activity-logs.index');
    });
});
