<?php

namespace Modules\Core\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Tenant Core module (Inventory tenant.php domain APIs).
 * Loads Module 1 tenant routes under /api after ResolveTenant.
 */
class CoreServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $tenantRoutes = base_path('routes/tenant.php');
        if (is_file($tenantRoutes)) {
            Route::middleware('api')
                ->prefix('api')
                ->group($tenantRoutes);
        }
    }

    public function register(): void
    {
        //
    }
}
