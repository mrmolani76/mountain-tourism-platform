<?php

use App\Http\Controllers\Api\HealthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/health', HealthController::class);

// Sanctum: current API user (placeholder route from install:api)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
