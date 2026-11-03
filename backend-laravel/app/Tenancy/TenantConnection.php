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

    /**
     * Point the tenant connection at a central registry row's `db_name`.
     */
    public static function useRegistryRow(object $tenant): void
    {
        $dbName = (string) (method_exists($tenant, 'getAttribute')
            ? $tenant->getAttribute('db_name')
            : ($tenant->db_name ?? ''));

        if ($dbName === '') {
            throw new \InvalidArgumentException('Tenant registry row has empty db_name.');
        }

        self::activate($dbName);
    }
}
