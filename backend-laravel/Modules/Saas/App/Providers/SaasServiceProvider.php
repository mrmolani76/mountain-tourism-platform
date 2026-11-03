<?php

namespace Modules\Saas\App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Central SaaS module — eQMS Complere folder + provider shape.
 */
class SaasServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Saas';

    protected string $moduleNameLower = 'saas';

    public function boot(): void
    {
        $this->registerConfig();
        $this->loadMigrationsFrom(base_path('Modules/Saas/Database/migrations'));
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            base_path('Modules/Saas/config/config.php'),
            $this->moduleNameLower
        );
    }
}
