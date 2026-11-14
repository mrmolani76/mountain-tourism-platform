<?php

namespace Modules\Reports\App\Providers;

use Illuminate\Support\ServiceProvider;

class ReportsServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Reports';

    protected string $moduleNameLower = 'reports';

    public function boot(): void
    {
        $this->mergeConfigFrom(
            base_path('Modules/Reports/config/config.php'),
            $this->moduleNameLower
        );

                // Landlord only: top-level *_*.php. Tenant schema under migrations/tenant/
        // is applied by tenants:provision-local / tenancy.migration_parameters (EQMS).
        $landlordMigrations = base_path('Modules/Reports/Database/migrations');
        if (is_dir($landlordMigrations) && glob($landlordMigrations.'/*_*.php')) {
            $this->loadMigrationsFrom($landlordMigrations);
        }
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
