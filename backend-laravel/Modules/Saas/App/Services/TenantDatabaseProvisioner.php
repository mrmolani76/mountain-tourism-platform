<?php

namespace Modules\Saas\App\Services;

use App\Tenancy\TenantConnection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\Saas\App\Exceptions\TenantDatabaseAlreadyExistsException;
use Modules\Saas\App\Exceptions\TenantDatabaseProvisioningException;
use Modules\Saas\App\Models\Tenant;
use Throwable;

class TenantDatabaseProvisioner
{
    private const REQUIRED_CORE_TABLES = [
        'core_staff',
        'core_building',
        'core_outlet',
        'core_season',
        'core_tenant_settings',
    ];

    /**
     * Create and migrate a new tenant database.
     *
     * Returns the created database name so the caller can roll back only this attempt.
     */
    public function provision(Tenant $tenant): ?string
    {
        $centralConnection = (string) config('tenancy.central_connection', config('database.default'));
        $central = DB::connection($centralConnection);
        $driver = $central->getDriverName();

        if ($driver === 'sqlite' && app()->environment('testing')) {
            return null;
        }

        if ($driver !== 'pgsql') {
            throw new TenantDatabaseProvisioningException(
                'Automatic tenant database provisioning requires PostgreSQL.'
            );
        }

        $databaseName = (string) $tenant->db_name;
        $this->assertSafeDatabaseName($databaseName);

        try {
            $exists = $central->table('pg_database')->where('datname', $databaseName)->exists();
        } catch (Throwable $exception) {
            Log::error('Could not check whether a tenant database already exists.', [
                'tenant_uuid' => $tenant->uuid,
                'exception_class' => get_class($exception),
            ]);

            throw new TenantDatabaseProvisioningException(
                'The tenant database could not be checked. No existing database was changed.',
                previous: $exception
            );
        }

        if ($exists) {
            throw new TenantDatabaseAlreadyExistsException(
                'A database with this tenant name already exists. It was not changed.'
            );
        }

        try {
            $central->statement('CREATE DATABASE "'.$databaseName.'"');
        } catch (Throwable $exception) {
            Log::error('Could not create a new tenant database.', [
                'tenant_uuid' => $tenant->uuid,
                'database' => $databaseName,
                'exception_class' => get_class($exception),
            ]);

            throw new TenantDatabaseProvisioningException(
                'The new tenant database could not be created.',
                previous: $exception
            );
        }

        $connectionName = (string) config('tenancy.connection_name', 'tenant');
        $previousConnection = config("database.connections.{$connectionName}");

        try {
            TenantConnection::useRegistryRow($tenant);
            $this->runTenantMigrations($connectionName);
            $this->verifyCoreSchema($connectionName);
        } catch (Throwable $exception) {
            $this->restoreTenantConnection($connectionName, $previousConnection);

            try {
                $this->dropDatabase($centralConnection, $databaseName);
            } catch (Throwable $cleanupException) {
                Log::critical('Could not remove a tenant database after migration failure.', [
                    'tenant_uuid' => $tenant->uuid,
                    'database' => $databaseName,
                    'exception_class' => get_class($cleanupException),
                ]);
            }

            Log::error('Tenant database migration failed.', [
                'tenant_uuid' => $tenant->uuid,
                'database' => $databaseName,
                'exception_class' => get_class($exception),
            ]);

            throw new TenantDatabaseProvisioningException(
                'Tenant migrations failed. The tenant was not activated.',
                previous: $exception
            );
        } finally {
            $this->restoreTenantConnection($connectionName, $previousConnection);
        }

        return $databaseName;
    }

    public function rollbackCreatedDatabase(string $databaseName): void
    {
        $this->assertSafeDatabaseName($databaseName);
        $centralConnection = (string) config('tenancy.central_connection', config('database.default'));
        $this->dropDatabase($centralConnection, $databaseName);
    }

    private function runTenantMigrations(string $connectionName): void
    {
        $paths = config('tenancy.migration_parameters.--path', []);
        if (! is_array($paths) || $paths === []) {
            throw new TenantDatabaseProvisioningException(
                'No tenant migration paths are configured.'
            );
        }

        $validPaths = array_values(array_filter(
            $paths,
            fn (mixed $path): bool => is_string($path) && $path !== '' && is_dir($path)
        ));

        if ($validPaths === []) {
            throw new TenantDatabaseProvisioningException(
                'No configured tenant migration paths are available.'
            );
        }

        foreach ($validPaths as $path) {
            $exitCode = Artisan::call('migrate', [
                '--database' => $connectionName,
                '--path' => $path,
                '--realpath' => true,
                '--force' => true,
            ]);

            if ($exitCode !== 0) {
                throw new TenantDatabaseProvisioningException(
                    'A configured tenant migration path failed.'
                );
            }
        }
    }

    private function verifyCoreSchema(string $connectionName): void
    {
        $schema = Schema::connection($connectionName);

        foreach (self::REQUIRED_CORE_TABLES as $table) {
            if (! $schema->hasTable($table)) {
                throw new TenantDatabaseProvisioningException(
                    'Required Core tenant tables are missing after migration.'
                );
            }
        }

        if (! $schema->hasColumn('core_staff', 'force_reset_password')) {
            throw new TenantDatabaseProvisioningException(
                'The tenant Core schema does not support first-login password reset.'
            );
        }
    }

    private function restoreTenantConnection(string $connectionName, mixed $previousConnection): void
    {
        if (is_array($previousConnection)) {
            Config::set("database.connections.{$connectionName}", $previousConnection);
        }

        DB::purge($connectionName);
    }

    private function dropDatabase(string $centralConnection, string $databaseName): void
    {
        DB::connection($centralConnection)->statement(
            'DROP DATABASE IF EXISTS "'.$databaseName.'"'
        );
    }

    private function assertSafeDatabaseName(string $databaseName): void
    {
        if (
            $databaseName === ''
            || strlen($databaseName) > 63
            || ! preg_match('/\A[a-zA-Z0-9_-]+\z/', $databaseName)
        ) {
            throw new TenantDatabaseProvisioningException(
                'The generated tenant database name is invalid.'
            );
        }
    }
}
