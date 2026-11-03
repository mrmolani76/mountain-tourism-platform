<?php

namespace App\Support\Tenancy;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

/**
 * Runtime DB-per-tenant connection switching.
 *
 * Port of Django `tenancy/connections.py` + `config/db_router.py`:
 * one PostgreSQL database per tenant, resolved per request. Isolation comes
 * from the active connection — no tenant_id columns anywhere (audit §1).
 *
 * The `tenant` connection is assembled by cloning the configured default
 * (central) connection and overriding the tenant's physical database name.
 */
class TenantConnection
{
    /**
     * Point the `tenant` connection at the tenant's physical PostgreSQL DB.
     */
    public static function use(Tenant $tenant): void
    {
        $central = self::centralName();

        $base = config()->get('database.connections.' . $central, []);

        config()->set(
            'database.connections.' . config('tenancy.connection_name'),
            array_merge($base, ['database' => $tenant->getAttribute('db_name')])
        );

        DB::purge(config('tenancy.connection_name'));
    }

    /**
     * Raw override for CLI contexts (seeders, artisan commands) operating on
     * a tenant registry row without an HTTP request.
     */
    public static function useRegistryRow(Tenant $tenant): void
    {
        self::use($tenant);
    }

    /**
     * The configured default (central) connection name, e.g. `pgsql`.
     */
    public static function centralName(): string
    {
        return (string) config('database.default');
    }
}
