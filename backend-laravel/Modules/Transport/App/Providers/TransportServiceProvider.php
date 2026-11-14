<?php

namespace Modules\Transport\App\Providers;

use Illuminate\Support\ServiceProvider;

class TransportServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Transport';

    protected string $moduleNameLower = 'transport';

    public function boot(): void
    {
                // Landlord only: top-level *_*.php. Tenant schema under migrations/tenant/
        // is applied by tenants:provision-local / tenancy.migration_parameters (EQMS).
        $landlordMigrations = base_path('Modules/Transport/Database/migrations');
        if (is_dir($landlordMigrations) && glob($landlordMigrations.'/*_*.php')) {
            $this->loadMigrationsFrom($landlordMigrations);
        }
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
