<?php

namespace Modules\Rental\App\Providers;

use Illuminate\Support\ServiceProvider;

class RentalServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Rental';

    protected string $moduleNameLower = 'rental';

    public function boot(): void
    {
        $landlordMigrations = base_path('Modules/Rental/Database/migrations');
        if (is_dir($landlordMigrations)) {
            $this->loadMigrationsFrom($landlordMigrations);
        }
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
