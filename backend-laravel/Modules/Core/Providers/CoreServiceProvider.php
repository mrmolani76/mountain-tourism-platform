<?php

namespace Modules\Core\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Tenant Core module (eQMS layout).
 *
 * Tenant migrations live only under Database/migrations/tenant/ — they are NOT
 * loaded here (Laravel loadMigrationsFrom is non-recursive). They run via
 * `tenants:migrate` / tenancy.migration_parameters paths (see config/tenancy.php).
 */
class CoreServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Landlord-only files would go in Database/migrations/*.php (none for Core MVP).
        $landlord = __DIR__ . '/../Database/migrations';
        if (is_dir($landlord)) {
            $this->loadMigrationsFrom($landlord);
        }

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
