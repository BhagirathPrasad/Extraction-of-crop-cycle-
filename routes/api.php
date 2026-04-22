<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CropCycleController;
use App\Http\Controllers\Api\DatasetController;
use App\Http\Controllers\Api\NdviController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

// ─── API v1 ───────────────────────────────────────────────────────────────────

Route::prefix('v1')->group(function () {

    // Authentication (public)
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login',    [AuthController::class, 'login']);
    });

    // Protected endpoints
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me',      [AuthController::class, 'me']);

        // Datasets
        Route::apiResource('datasets', DatasetController::class);

        // Crop Cycles
        Route::apiResource('crop-cycles', CropCycleController::class);

        // NDVI
        Route::get('/ndvi/{cropCycle}',    [NdviController::class, 'index']);
        Route::get('/dashboard/stats',     [NdviController::class, 'dashboardStats']);

        // Reports
        Route::apiResource('reports', ReportController::class)->only(['index', 'store', 'show', 'destroy']);
        Route::get('/reports/{report}/download', [\App\Http\Controllers\ReportController::class, 'download']);
    });
});
