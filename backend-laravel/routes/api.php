<?php

/**
 * Central API surface (Inventory `routes/api.php` pattern).
 *
 * - Health is public on any host.
 * - SaaS (Module Saas) routes are central-only: complexes, owners, activate/deactivate.
 * - Tenant business APIs live in Modules/Core (and later B–I modules), loaded by
 *   CoreServiceProvider — same Host→tenant resolve as Inventory tenant.php.
 */

use App\Http\Controllers\Api\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

$saasRoutes = base_path('Modules/Saas/Routes/api.php');
if (is_file($saasRoutes)) {
    require $saasRoutes;
}
