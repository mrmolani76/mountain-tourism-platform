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

        $landlordMigrations = base_path('Modules/Reports/Database/migrations');
        if (is_dir($landlordMigrations)) {
            $this->loadMigrationsFrom($landlordMigrations);
        }
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
