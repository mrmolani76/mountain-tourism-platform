<?php

namespace Modules\Folio\App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

/**
 * Module route map for tenant Folio APIs.
 * EQMS/Nexro: prevent central hosts, then Host→tenant DB resolve.
 */
class RouteServiceProvider extends ServiceProvider
{
    protected string $moduleNamespace = 'Modules\\Folio\\App\\Http\\Controllers';

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
            ->middleware([
                'api',
                'tenancy.prevent_central',
                'resolve.tenant',
                'tenancy.ensure_resolved',
            ])
            ->namespace($this->moduleNamespace)
            ->group(base_path('Modules/Folio/routes/api.php'));
    }
}
