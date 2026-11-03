<?php

namespace Modules\Saas\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Central SaaS module (Inventory central DB + routes/api.php SaaS group).
 * Routes are required from routes/api.php so central stays one entrypoint.
 */
class SaasServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    public function register(): void
    {
        //
    }
}
