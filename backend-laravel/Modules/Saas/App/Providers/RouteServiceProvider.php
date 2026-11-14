<?php

namespace Modules\Saas\App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

/**
 * Central SaaS APIs — domain-bound like EQMS/Nexro landlord routes.
 */
class RouteServiceProvider extends ServiceProvider
{
    protected string $moduleNamespace = 'Modules\\Saas\\App\\Http\\Controllers';

    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        $this->mapApiRoutes();
    }

    protected function mapApiRoutes(): void
    {
        $centrals = array_values(array_unique(array_filter(
            array_map('strval', (array) config('tenancy.central_domains', []))
        )));

        $middleware = ['api', 'tenancy.prevent_tenant'];
        $path = base_path('Modules/Saas/routes/api.php');
        $namespace = $this->moduleNamespace;

        if ($centrals === []) {
            Route::prefix('api')
                ->middleware($middleware)
                ->namespace($namespace)
                ->group($path);

            return;
        }

        foreach ($centrals as $host) {
            Route::domain($host)
                ->prefix('api')
                ->middleware($middleware)
                ->namespace($namespace)
                ->group($path);
        }
    }
}
