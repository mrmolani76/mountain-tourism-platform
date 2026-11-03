<?php

namespace Modules\Saas\App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

/**
 * eQMS Complere-style module route map (central SaaS APIs).
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
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->moduleNamespace)
            ->group(base_path('Modules/Saas/routes/api.php'));
    }
}
