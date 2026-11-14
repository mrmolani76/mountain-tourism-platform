<?php

use Illuminate\Support\Facades\Route;
use Modules\Weather\App\Http\Controllers\AvalancheController;
use Modules\Weather\App\Http\Controllers\LobbyController;
use Modules\Weather\App\Http\Controllers\WeatherCancellationController;
use Modules\Weather\App\Http\Controllers\WeatherLimitController;
use Modules\Weather\App\Http\Controllers\WeatherPointController;
use Modules\Weather\App\Http\Controllers\WeatherWarningController;

Route::get('weather/lobby/{token}', [LobbyController::class, 'show']);

Route::middleware('staff.auth')->prefix('weather')->group(function () {
    Route::get('points', [WeatherPointController::class, 'index']);
    Route::post('points', [WeatherPointController::class, 'store']);
    Route::patch('points/{point}', [WeatherPointController::class, 'update']);
    Route::post('points/{point}/fetch', [WeatherPointController::class, 'fetch']);
    Route::post('points/{point}/evaluate', [WeatherPointController::class, 'evaluate']);

    Route::get('limits', [WeatherLimitController::class, 'index']);
    Route::post('limits', [WeatherLimitController::class, 'store']);
    Route::patch('limits/{limit}', [WeatherLimitController::class, 'update']);

    Route::get('warnings', [WeatherWarningController::class, 'index']);
    Route::post('warnings/{warning}/resolve', [WeatherWarningController::class, 'resolve']);

    Route::post('cancellations', [WeatherCancellationController::class, 'store']);

    Route::get('avalanche', [AvalancheController::class, 'index']);
    Route::post('avalanche', [AvalancheController::class, 'upsert']);
    Route::put('avalanche', [AvalancheController::class, 'upsert']);
});
