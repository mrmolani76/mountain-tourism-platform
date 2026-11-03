<?php

namespace App\Tenancy;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * Points the runtime `tenant` connection at a physical tenant database.
 * Inventory/stancl DatabaseTenancyBootstrapper equivalent for the custom bridge.
 */
class TenantConnection
{
    public static function activate(string $dbName): void
    {
        $central = config('tenancy.central_connection', config('database.default'));
        $base = config("database.connections.{$central}");

        Config::set('database.connections.'.config('tenancy.connection_name'), array_merge($base, [
            'database' => $dbName,
        ]));

        DB::purge(config('tenancy.connection_name'));
        DB::reconnect(config('tenancy.connection_name'));
    }
}
