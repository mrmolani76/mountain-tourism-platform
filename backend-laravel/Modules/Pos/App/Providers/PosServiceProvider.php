<?php

namespace Modules\Pos\App\Providers;

use Illuminate\Support\ServiceProvider;

class PosServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Pos';

    protected string $moduleNameLower = 'pos';

    public function boot(): void
    {
        $landlordMigrations = base_path('Modules/Pos/Database/migrations');
        if (is_dir($landlordMigrations)) {
            $this->loadMigrationsFrom($landlordMigrations);
        }
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
