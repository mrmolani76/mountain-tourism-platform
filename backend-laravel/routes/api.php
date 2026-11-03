<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BuildingController;
use App\Http\Controllers\Api\HealthController;
use Illuminate\Support\Facades\Route;

// Public — no tenant resolution required (HealthView has its own handling).
Route::get('/health', HealthController::class);

// Tenant-resolved routes. On central hosts resolve() returns null (no abort),
// so login can still surface "Staff login requires a tenant host." (DRF parity).
Route::group([], function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::middleware('staff.auth')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

        // Owner-gated inside the controller (DRF IsOwner on all actions).
        // PUT is intentionally not registered -> MethodNotAllowed 405 parity.
        Route::get('/buildings', [BuildingController::class, 'index']);
        Route::post('/buildings', [BuildingController::class, 'store']);
        Route::get('/buildings/{building}', [BuildingController::class, 'show']);
        Route::patch('/buildings/{building}', [BuildingController::class, 'update']);
        Route::delete('/buildings/{building}', [BuildingController::class, 'destroy']);
    });
});
