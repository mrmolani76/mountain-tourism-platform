<?php

/**
 * App-level central routes only (health).
 * Module routes load via eQMS-style Module RouteServiceProviders:
 *   - Modules/Saas → central SaaS
 *   - Modules/Core → tenant Core
 */

use App\Http\Controllers\Api\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);
