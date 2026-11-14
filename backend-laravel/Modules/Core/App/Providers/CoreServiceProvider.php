<?php

namespace Modules\Core\App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Tenant Core module provider.
 */
class CoreServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Core';

    protected string $moduleNameLower = 'core';

    public function boot(): void
    {
        $this->registerConfig();
        // Landlord only: top-level *_*.php. Tenant schema under migrations/tenant/
        // is applied by tenants:provision-local / tenancy.migration_parameters (EQMS).
        $landlordMigrations = base_path('Modules/Core/Database/migrations');
        if (is_dir($landlordMigrations) && glob($landlordMigrations.'/*_*.php')) {
            $this->loadMigrationsFrom($landlordMigrations);
        }
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\Core\App\Console\SeedFoundationCommand::class,
            ]);
        }
    }

    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            base_path('Modules/Core/config/config.php'),
            $this->moduleNameLower
        );

        // Modules/Core/config/menu.php → config('menu.items')
        $this->mergeConfigFrom(
            base_path('Modules/Core/config/menu.php'),
            'menu'
        );

        // Modules/Core/config/listing.php → config('listing.users')
        $this->mergeConfigFrom(
            base_path('Modules/Core/config/listing.php'),
            'listing'
        );
    }
}
