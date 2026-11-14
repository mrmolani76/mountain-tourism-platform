<?php

namespace Modules\Weather\App\Providers;

use Illuminate\Support\ServiceProvider;

class WeatherServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Weather';

    protected string $moduleNameLower = 'weather';

    public function boot(): void
    {
        $this->mergeConfigFrom(
            base_path('Modules/Weather/config/config.php'),
            $this->moduleNameLower
        );

                // Landlord only: top-level *_*.php. Tenant schema under migrations/tenant/
        // is applied by tenants:provision-local / tenancy.migration_parameters (EQMS).
        $landlordMigrations = base_path('Modules/Weather/Database/migrations');
        if (is_dir($landlordMigrations) && glob($landlordMigrations.'/*_*.php')) {
            $this->loadMigrationsFrom($landlordMigrations);
        }
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
