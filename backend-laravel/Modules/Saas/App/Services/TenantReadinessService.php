<?php

namespace Modules\Saas\App\Services;

use App\Tenancy\TenantConnection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Modules\Core\App\Enums\StaffRole;
use Modules\Core\App\Models\Staff;
use Modules\Saas\App\Models\Tenant;
use PDOException;

class TenantReadinessService
{
    private const REQUIRED_CORE_TABLES = [
        'core_staff',
        'core_building',
        'core_outlet',
        'core_season',
        'core_tenant_settings',
    ];

    public function forTenant(Tenant $tenant): array
    {
        $tenantConnection = (string) config('tenancy.connection_name', 'tenant');
        $previousConnection = config("database.connections.{$tenantConnection}");
        $databaseConnected = false;
        $migrationsReady = false;
        $ownerReady = false;
        $probeFailed = false;

        try {
            TenantConnection::useRegistryRow($tenant);
            DB::connection($tenantConnection)->getPdo();
            $databaseConnected = true;

            $schema = Schema::connection($tenantConnection);
            $missingTables = array_values(array_filter(
                self::REQUIRED_CORE_TABLES,
                fn (string $table): bool => ! $schema->hasTable($table)
            ));
            $ownerPasswordFlagReady = $schema->hasTable('core_staff')
                && $schema->hasColumn('core_staff', 'force_reset_password');

            $migrationsReady = $schema->hasTable('migrations')
                && $missingTables === []
                && $ownerPasswordFlagReady;
            $ownerReady = $schema->hasTable('core_staff')
                && Staff::on($tenantConnection)
                    ->where('role', StaffRole::OWNER)
                    ->where('is_active', true)
                    ->exists();
        } catch (QueryException|PDOException|InvalidArgumentException $exception) {
            Log::warning('SaaS tenant readiness probe did not complete.', [
                'tenant_id' => $tenant->getKey(),
                'exception_class' => get_class($exception),
            ]);
            $probeFailed = true;
        } finally {
            if (is_array($previousConnection)) {
                Config::set("database.connections.{$tenantConnection}", $previousConnection);
                DB::purge($tenantConnection);
            }
        }

        $hasPrimaryDomain = $tenant->domains->contains(fn ($domain): bool => (bool) $domain->is_primary);
        $databaseReady = $databaseConnected && ! $probeFailed;

        return [
            'registry' => ['ready' => true],
            'primary_domain' => [
                'ready' => $hasPrimaryDomain,
                'next_step' => $hasPrimaryDomain ? null : 'Add a primary domain to the tenant registry.',
            ],
            'tenant_database' => [
                'ready' => $databaseReady,
                'state' => $databaseReady ? 'connected' : ($probeFailed ? 'unavailable' : 'not_configured'),
                'next_step' => $databaseReady
                    ? null
                    : 'Provision the tenant database and verify the central registry database name.',
            ],
            'tenant_migrations' => [
                'ready' => $migrationsReady,
                'next_step' => $migrationsReady
                    ? null
                    : 'Run the approved tenant migrations, including the Core tables.',
            ],
            'owner_account' => [
                'ready' => $ownerReady,
                'next_step' => $ownerReady
                    ? null
                    : 'Create or activate an Owner staff account after tenant migrations are ready.',
            ],
            'ready' => $hasPrimaryDomain && $databaseReady && $migrationsReady && $ownerReady,
        ];
    }
}
