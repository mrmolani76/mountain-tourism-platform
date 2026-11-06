<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\App\Http\Controllers\AuthController;
use Modules\Core\App\Http\Controllers\BuildingController;
use Modules\Core\App\Http\Controllers\CoreListingController;
use Modules\Core\App\Http\Controllers\MenuController;
use Modules\Core\App\Http\Controllers\OutletAccessController;
use Modules\Core\App\Http\Controllers\OutletController;
use Modules\Core\App\Http\Controllers\SeasonController;
use Modules\Core\App\Http\Controllers\SettingsController;
use Modules\Core\App\Http\Controllers\StaffController;
use Modules\Core\App\Http\Controllers\UserListingController;

// Trailing-slash aliases keep older Nuxt clients working (DRF used `/auth/login/`).
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/login/', [AuthController::class, 'login']);
Route::post('auth/logout', [AuthController::class, 'logout']);
Route::post('auth/logout/', [AuthController::class, 'logout']);

Route::middleware('staff.auth')->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::get('auth/me/', [AuthController::class, 'me']);
    Route::post('auth/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('auth/reset-password/', [AuthController::class, 'resetPassword']);

    // GET /api/core/menus
    Route::get('core/menus', [MenuController::class, 'index']);
    Route::get('core/menus/', [MenuController::class, 'index']);

    // EQMS DataTable listing (before {id} routes).
    Route::get('buildings/list/config', [CoreListingController::class, 'listingConfig'])
        ->defaults('resource', 'buildings');
    Route::post('buildings/list', [CoreListingController::class, 'list'])
        ->defaults('resource', 'buildings');
    Route::get('seasons/list/config', [CoreListingController::class, 'listingConfig'])
        ->defaults('resource', 'seasons');
    Route::post('seasons/list', [CoreListingController::class, 'list'])
        ->defaults('resource', 'seasons');

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

    // Staff listing / form / view surface.
    Route::get('users/list/config', [UserListingController::class, 'listingConfig']);
    Route::post('users/list', [UserListingController::class, 'list']);
    Route::post('users/create', [UserListingController::class, 'create']);
    Route::post('users/destroy', [UserListingController::class, 'destroy']);
    Route::post('users/usersActivate', [UserListingController::class, 'activate']);
    Route::post('users/usersDeactivate', [UserListingController::class, 'deactivate']);
    Route::get('user/fetch', [UserListingController::class, 'fetch']);
    Route::get('user/show', [UserListingController::class, 'show']);
    Route::post('user/update', [UserListingController::class, 'update']);
    Route::get('user/formInitialData', [UserListingController::class, 'formInitialData']);

    Route::get('outlet-access', [OutletAccessController::class, 'index']);
    Route::post('outlet-access', [OutletAccessController::class, 'store']);
    Route::delete('outlet-access/{outlet_access}', [OutletAccessController::class, 'destroy']);

    Route::get('settings', [SettingsController::class, 'show']);
    Route::patch('settings', [SettingsController::class, 'update']);
});
