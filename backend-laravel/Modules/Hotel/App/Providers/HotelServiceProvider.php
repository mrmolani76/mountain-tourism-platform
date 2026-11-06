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
        $landlordMigrations = base_path('Modules/Hotel/Database/migrations');
        if (is_dir($landlordMigrations)) {
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
