<?php

namespace Modules\Reports\App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $moduleNamespace = 'Modules\\Reports\\App\\Http\\Controllers';

    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->moduleNamespace)
            ->group(base_path('Modules/Reports/routes/api.php'));
    }
}
