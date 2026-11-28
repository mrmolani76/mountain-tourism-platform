<?php

use Illuminate\Support\Facades\Route;
use Modules\Saas\App\Http\Controllers\SaasAuthController;
use Modules\Saas\App\Http\Controllers\TenantController;

Route::prefix('saas')->group(function () {
    Route::post('auth/login', [SaasAuthController::class, 'login']);
    Route::post('auth/logout', [SaasAuthController::class, 'logout']);

    Route::middleware('saas.auth')->group(function () {
        Route::get('auth/me', [SaasAuthController::class, 'me']);
        Route::get('dashboard', [TenantController::class, 'dashboard']);
        Route::get('tenants', [TenantController::class, 'index']);
        Route::post('tenants', [TenantController::class, 'store']);
        Route::get('tenants/{tenantUuid}', [TenantController::class, 'show']);
        Route::post('tenants/{tenantUuid}/owner', [TenantController::class, 'createOwner']);
        Route::get('tenants/{tenantUuid}/readiness', [TenantController::class, 'readiness']);
        Route::get('tenants/{tenantUuid}/activity', [TenantController::class, 'activity']);
        Route::patch('tenants/{tenantUuid}', [TenantController::class, 'update']);
        Route::post('tenants/{tenantUuid}/activate', [TenantController::class, 'activate']);
        Route::post('tenants/{tenantUuid}/deactivate', [TenantController::class, 'deactivate']);
    });
});
