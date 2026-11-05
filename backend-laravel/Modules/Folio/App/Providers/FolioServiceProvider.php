<?php

namespace Modules\Folio\App\Providers;

use Illuminate\Support\ServiceProvider;

class FolioServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Folio';

    protected string $moduleNameLower = 'folio';

    public function boot(): void
    {
        $landlordMigrations = base_path('Modules/Folio/Database/migrations');
        if (is_dir($landlordMigrations)) {
            $this->loadMigrationsFrom($landlordMigrations);
        }
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
