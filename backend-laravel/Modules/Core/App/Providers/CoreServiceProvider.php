<?php

namespace Modules\Core\App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Tenant Core module — eQMS Complere folder + provider shape.
 */
class CoreServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Core';

    protected string $moduleNameLower = 'core';

    public function boot(): void
    {
        $this->registerConfig();
        // Landlord-only PHP files in Database/migrations/*.php (none for Core MVP).
        // Tenant files live in Database/migrations/tenant/ and run via tenants:migrate.
        $landlordMigrations = base_path('Modules/Core/Database/migrations');
        if (is_dir($landlordMigrations)) {
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
    }
}
