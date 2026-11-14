<?php

namespace Modules\Hotel\App\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Hotel\App\Console\NightlyPostingCommand;

class HotelServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Hotel';

    protected string $moduleNameLower = 'hotel';

    public function boot(): void
    {
                // Landlord only: top-level *_*.php. Tenant schema under migrations/tenant/
        // is applied by tenants:provision-local / tenancy.migration_parameters (EQMS).
        $landlordMigrations = base_path('Modules/Hotel/Database/migrations');
        if (is_dir($landlordMigrations) && glob($landlordMigrations.'/*_*.php')) {
            $this->loadMigrationsFrom($landlordMigrations);
        }
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);

        $this->mergeConfigFrom(
            base_path('Modules/Hotel/config/listing.php'),
            'hotel_listing'
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                NightlyPostingCommand::class,
            ]);
        }
    }
}
