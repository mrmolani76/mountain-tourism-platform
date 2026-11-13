<?php

use Illuminate\Support\Facades\Route;
use Modules\Transport\App\Http\Controllers\DriverController;
use Modules\Transport\App\Http\Controllers\LiftController;
use Modules\Transport\App\Http\Controllers\LiftPassController;
use Modules\Transport\App\Http\Controllers\RouteController;
use Modules\Transport\App\Http\Controllers\TripController;
use Modules\Transport\App\Http\Controllers\VehicleController;

Route::get('transport/lobby/lifts', [LiftController::class, 'lobby']);

Route::middleware('staff.auth')->prefix('transport')->group(function () {
    Route::get('vehicles', [VehicleController::class, 'index']);
    Route::post('vehicles', [VehicleController::class, 'store']);
    Route::patch('vehicles/{vehicle}', [VehicleController::class, 'update']);

    Route::get('drivers', [DriverController::class, 'index']);
    Route::post('drivers', [DriverController::class, 'store']);
    Route::patch('drivers/{driver}', [DriverController::class, 'update']);

    Route::get('routes', [RouteController::class, 'index']);
    Route::post('routes', [RouteController::class, 'store']);
    Route::patch('routes/{route}', [RouteController::class, 'update']);

    Route::get('trips', [TripController::class, 'index']);
    Route::post('trips', [TripController::class, 'store']);
    Route::post('trips/pickup-from-reservation', [TripController::class, 'pickupFromReservation']);
    Route::get('trips/{trip}', [TripController::class, 'show']);
    Route::post('trips/{trip}/passengers', [TripController::class, 'addPassenger']);
    Route::get('trips/{trip}/passenger-list', [TripController::class, 'passengerList']);
    Route::post('passengers/{passenger}/cancel', [TripController::class, 'cancelPassenger']);

    Route::get('lifts', [LiftController::class, 'index']);
    Route::post('lifts', [LiftController::class, 'store']);
    Route::patch('lifts/{lift}', [LiftController::class, 'update']);
    Route::post('lifts/{lift}/status', [LiftController::class, 'changeStatus']);
    Route::get('lifts/{lift}/status-history', [LiftController::class, 'history']);

    Route::get('lift-pass-products', [LiftPassController::class, 'products']);
    Route::post('lift-pass-products', [LiftPassController::class, 'storeProduct']);
    Route::post('lift-passes', [LiftPassController::class, 'sell']);
});
