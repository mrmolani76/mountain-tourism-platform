<?php

namespace Modules\Core\App\Console;

use Illuminate\Console\Command;
use Modules\Core\App\Models\Building;
use Modules\Core\App\Models\Outlet;
use Modules\Core\Database\Seeders\FoundationSeeder;

/**
 * Dev seed: PDF §2 buildings + sample outlets on the active tenant connection.
 *
 * Usage (after tenants:provision-local / tenant DB ready):
 *   php artisan core:seed-foundation
 */
class SeedFoundationCommand extends Command
{
    protected $signature = 'core:seed-foundation
                            {--reset : Delete all buildings/outlets before seeding}';

    protected $description = 'Seed PDF §2 demo buildings + sample outlets (tenant DB)';

    public function handle(): int
    {
        if ($this->option('reset')) {
            Outlet::on(config('tenancy.connection_name'))->query()->delete();
            Building::on(config('tenancy.connection_name'))->query()->delete();
            $this->warn('Reset: all buildings/outlets deleted.');
        }

        (new FoundationSeeder)->run();

        $buildings = Building::on(config('tenancy.connection_name'))->count();
        $outlets = Outlet::on(config('tenancy.connection_name'))->count();
        $this->info("Seed complete: {$buildings} buildings, {$outlets} outlets present.");

        return self::SUCCESS;
    }
}
