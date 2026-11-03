<?php

namespace Modules\Core\App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

/**
 * eQMS Complere-style module route map (tenant Core APIs).
 * Tenancy Host resolve already runs on the global `api` middleware stack.
 */
class RouteServiceProvider extends ServiceProvider
{
    protected string $moduleNamespace = 'Modules\\Core\\App\\Http\\Controllers';

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
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->moduleNamespace)
            ->group(base_path('Modules/Core/routes/api.php'));
    }
}
