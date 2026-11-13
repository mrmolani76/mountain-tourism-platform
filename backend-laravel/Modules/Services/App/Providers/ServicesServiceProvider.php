<?php

namespace Modules\Services\App\Providers;

use Illuminate\Support\ServiceProvider;

class ServicesServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Services';

    protected string $moduleNameLower = 'services';

    public function boot(): void
    {
        $landlordMigrations = base_path('Modules/Services/Database/migrations');
        if (is_dir($landlordMigrations)) {
            $this->loadMigrationsFrom($landlordMigrations);
        }
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
