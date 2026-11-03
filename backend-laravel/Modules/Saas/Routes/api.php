<?php

use Illuminate\Support\Facades\Route;
use Modules\Saas\Http\Controllers\SaasAuthController;
use Modules\Saas\Http\Controllers\TenantController;

Route::prefix('saas')->group(function () {
    Route::post('auth/login', [SaasAuthController::class, 'login']);
    Route::post('auth/logout', [SaasAuthController::class, 'logout']);

    Route::middleware('saas.auth')->group(function () {
        Route::get('auth/me', [SaasAuthController::class, 'me']);
        Route::get('tenants', [TenantController::class, 'index']);
        Route::post('tenants', [TenantController::class, 'store']);
        Route::get('tenants/{tenant}', [TenantController::class, 'show']);
        Route::patch('tenants/{tenant}', [TenantController::class, 'update']);
        Route::post('tenants/{tenant}/activate', [TenantController::class, 'activate']);
        Route::post('tenants/{tenant}/deactivate', [TenantController::class, 'deactivate']);
    });
});
