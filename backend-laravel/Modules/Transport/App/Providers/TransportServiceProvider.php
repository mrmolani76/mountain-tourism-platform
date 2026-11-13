<?php

namespace Modules\Transport\App\Providers;

use Illuminate\Support\ServiceProvider;

class TransportServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Transport';

    protected string $moduleNameLower = 'transport';

    public function boot(): void
    {
        $landlordMigrations = base_path('Modules/Transport/Database/migrations');
        if (is_dir($landlordMigrations)) {
            $this->loadMigrationsFrom($landlordMigrations);
        }
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
