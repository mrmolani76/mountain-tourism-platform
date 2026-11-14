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

        $landlordMigrations = base_path('Modules/Weather/Database/migrations');
        if (is_dir($landlordMigrations)) {
            $this->loadMigrationsFrom($landlordMigrations);
        }
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
