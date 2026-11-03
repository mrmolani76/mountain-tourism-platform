<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\AuthController;
use Modules\Core\Http\Controllers\BuildingController;
use Modules\Core\Http\Controllers\OutletAccessController;
use Modules\Core\Http\Controllers\OutletController;
use Modules\Core\Http\Controllers\SeasonController;
use Modules\Core\Http\Controllers\SettingsController;
use Modules\Core\Http\Controllers\StaffController;

Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/logout', [AuthController::class, 'logout']);

Route::middleware('staff.auth')->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/reset-password', [AuthController::class, 'resetPassword']);

    Route::get('buildings', [BuildingController::class, 'index']);
    Route::post('buildings', [BuildingController::class, 'store']);
    Route::get('buildings/{building}', [BuildingController::class, 'show']);
    Route::patch('buildings/{building}', [BuildingController::class, 'update']);
    Route::delete('buildings/{building}', [BuildingController::class, 'destroy']);

    Route::apiResource('outlets', OutletController::class)->except(['update']);
    Route::patch('outlets/{outlet}', [OutletController::class, 'update']);

    Route::apiResource('seasons', SeasonController::class)->except(['update']);
    Route::patch('seasons/{season}', [SeasonController::class, 'update']);

    Route::apiResource('staff', StaffController::class)->except(['update']);
    Route::patch('staff/{staff}', [StaffController::class, 'update']);

    Route::get('outlet-access', [OutletAccessController::class, 'index']);
    Route::post('outlet-access', [OutletAccessController::class, 'store']);
    Route::delete('outlet-access/{outlet_access}', [OutletAccessController::class, 'destroy']);

    Route::get('settings', [SettingsController::class, 'show']);
    Route::patch('settings', [SettingsController::class, 'update']);
});
