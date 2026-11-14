<?php

namespace Modules\Folio\App\Providers;

use Illuminate\Support\ServiceProvider;

class FolioServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Folio';

    protected string $moduleNameLower = 'folio';

    public function boot(): void
    {
                // Landlord only: top-level *_*.php. Tenant schema under migrations/tenant/
        // is applied by tenants:provision-local / tenancy.migration_parameters (EQMS).
        $landlordMigrations = base_path('Modules/Folio/Database/migrations');
        if (is_dir($landlordMigrations) && glob($landlordMigrations.'/*_*.php')) {
            $this->loadMigrationsFrom($landlordMigrations);
        }
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
