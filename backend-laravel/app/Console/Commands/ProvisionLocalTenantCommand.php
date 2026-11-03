<?php

namespace App\Console\Commands;

use App\Tenancy\TenantConnection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Core\App\Enums\StaffRole;
use Modules\Saas\App\Models\Domain;
use Modules\Saas\App\Models\Tenant;

/**
 * Local-only tenant provision (eQMS resetLocalDatabase.sh spirit).
 *
 * Creates: physical Postgres DB + central registry/domain + tenant migrations + fixture staff.
 * Does NOT call AWS. Guarded to APP_ENV=local.
 *
 * DB name = "{slug}_local" (eQMS: "{url_prefix}_{APP_ENV}").
 * Host    = "{slug}.localhost" (dev shorthand of "{slug}.local.complere.local").
 */
class ProvisionLocalTenantCommand extends Command
{
    protected $signature = 'tenants:provision-local
                            {slug=lab : Tenant slug / url_prefix}
                            {--name= : Display name (default: "{slug} Complex")}
                            {--reset : Drop and recreate the tenant DB + re-seed staff}
                            {--with-saas-admin : Ensure super@saas.test exists on central}
                            {--host= : Override tenant hostname (default: {slug}.localhost)}';

    protected $description = 'Provision a local tenant DB + registry row + fixture staff (APP_ENV=local only)';

    public function handle(): int
    {
        if (config('app.env') !== 'local') {
            $this->error('Refusing: APP_ENV must be local.');

            return self::FAILURE;
        }

        $slug = strtolower((string) $this->argument('slug'));
        if (! preg_match('/^[a-z][a-z0-9_]{0,62}$/', $slug)) {
            $this->error('Invalid slug. Use lowercase letters, digits, underscore; start with a letter.');

            return self::FAILURE;
        }

        if ($slug === 'saas') {
            $this->error('slug "saas" is reserved for the central admin host.');

            return self::FAILURE;
        }

        $name = (string) ($this->option('name') ?: ucfirst($slug).' Complex');
        $dbName = $slug.'_local';
        $hostname = (string) ($this->option('host') ?: $slug.'.localhost');
        $reset = (bool) $this->option('reset');
        $central = config('tenancy.central_connection', config('database.default'));

        $this->info("Provisioning local tenant '{$slug}' → DB {$dbName}, host {$hostname}");

        if ($this->option('with-saas-admin')) {
            $this->ensureSaasAdmin($central);
        }

        $this->ensurePhysicalDatabase($central, $dbName, $reset);

        $tenant = $this->ensureRegistry($name, $slug, $dbName, $hostname);

        TenantConnection::useRegistryRow($tenant);
        $this->runTenantMigrations();
        $this->seedFixtureStaff();

        $this->newLine();
        $this->info('Done.');
        $this->table(
            ['slug', 'db_name', 'hostname', 'login'],
            [[
                $slug,
                $dbName,
                $hostname,
                "http://{$hostname}:3000/login  owner@{$slug}.test / password123",
            ]]
        );

        return self::SUCCESS;
    }

    private function ensureSaasAdmin(string $central): void
    {
        DB::connection($central)->table('saas_user')->updateOrInsert(
            ['email' => 'super@saas.test'],
            [
                'password' => Hash::make('password123'),
                'is_active' => true,
                'created_at' => now(),
            ]
        );
        $this->line('  · saas admin super@saas.test ensured');
    }

    private function ensurePhysicalDatabase(string $central, string $dbName, bool $reset): void
    {
        $exists = (bool) DB::connection($central)->selectOne(
            'SELECT 1 AS ok FROM pg_database WHERE datname = ?',
            [$dbName]
        );

        if ($exists && $reset) {
            // Terminate sessions then drop (Postgres).
            DB::connection($central)->statement(
                'SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname = ? AND pid <> pg_backend_pid()',
                [$dbName]
            );
            DB::connection($central)->statement('DROP DATABASE IF EXISTS "'.$dbName.'"');
            $exists = false;
            $this->line("  · dropped {$dbName}");
        }

        if (! $exists) {
            DB::connection($central)->statement('CREATE DATABASE "'.$dbName.'"');
            $this->line("  · created {$dbName}");
        }
        else {
            $this->line("  · database {$dbName} already exists");
        }
    }

    private function ensureRegistry(string $name, string $slug, string $dbName, string $hostname): Tenant
    {
        $tenant = Tenant::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'db_name' => $dbName,
                'is_active' => true,
            ]
        );

        Domain::query()->updateOrCreate(
            ['hostname' => $hostname],
            [
                'tenant_id' => $tenant->getKey(),
                'is_primary' => true,
            ]
        );

        // Optional alias so bare tooling still resolves (DevPilotSeeder also maps localhost → lab).
        if ($slug === 'lab') {
            Domain::query()->updateOrCreate(
                ['hostname' => 'localhost'],
                [
                    'tenant_id' => $tenant->getKey(),
                    'is_primary' => false,
                ]
            );
        }

        $this->line('  · central registry + domain ensured');

        return $tenant->fresh();
    }

    private function runTenantMigrations(): void
    {
        $paths = config('tenancy.migration_parameters.--path', []);
        if (! is_array($paths) || $paths === []) {
            $paths = array_values(array_filter(array_merge(
                glob(database_path('migrations/tenant')) ?: [],
                glob(base_path('Modules/*/Database/migrations/tenant')) ?: [],
            )));
        }

        $conn = config('tenancy.connection_name', 'tenant');

        foreach ($paths as $path) {
            if (! is_string($path) || $path === '' || ! is_dir($path)) {
                continue;
            }

            $this->call('migrate', [
                '--database' => $conn,
                '--path' => $path,
                '--realpath' => true,
                '--force' => true,
            ]);
        }

        $this->line('  · tenant migrations applied');
    }

    private function seedFixtureStaff(): void
    {
        $conn = config('tenancy.connection_name', 'tenant');
        $slug = strtolower((string) $this->argument('slug'));

        $staffRows = [
            ['email' => "owner@{$slug}.test", 'role' => StaffRole::OWNER],
            ['email' => "manager@{$slug}.test", 'role' => StaffRole::MANAGER],
            ['email' => "desk@{$slug}.test", 'role' => StaffRole::FRONT_DESK],
            ['email' => "cashier@{$slug}.test", 'role' => StaffRole::CASHIER],
            ['email' => "rental@{$slug}.test", 'role' => StaffRole::RENTAL_DESK],
            ['email' => "service@{$slug}.test", 'role' => StaffRole::SERVICE_STAFF],
            ['email' => "transport@{$slug}.test", 'role' => StaffRole::TRANSPORT_DESK],
            ['email' => "lift@{$slug}.test", 'role' => StaffRole::LIFT_OPERATOR],
        ];

        foreach ($staffRows as $row) {
            DB::connection($conn)->table('core_staff')->updateOrInsert(
                ['email' => $row['email']],
                [
                    'password' => Hash::make('password123'),
                    'role' => $row['role'],
                    'is_active' => true,
                    'created_at' => now(),
                ]
            );
        }

        $this->line('  · fixture staff seeded (password123)');
    }
}
