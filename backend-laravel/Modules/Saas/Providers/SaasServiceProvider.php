<?php

namespace Modules\Saas\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Central SaaS module.
 * Landlord migrations: Modules/Saas/Database/migrations/ (loaded on `php artisan migrate`).
 */
class SaasServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');
    }

    public function register(): void
    {
        //
    }
}
