<?php

namespace Tests\Support;

use App\Tenancy\TenantConnection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Modules\Core\App\Enums\StaffRole;
use Modules\Core\App\Models\Staff;
use Modules\Saas\App\Models\Domain;
use Modules\Saas\App\Models\SaasUser;
use Modules\Saas\App\Models\Tenant;
use Tests\TestCase;

/**
 * Dual-connection SQLite harness for Module 1 (central registry + one tenant DB).
 * Mirrors Django lab-tenant fixtures without Docker.
 */
abstract class TenancyTestCase extends TestCase
{
    protected string $centralPath;

    protected string $tenantPath;

    protected Tenant $labTenant;

    protected Staff $owner;

    protected Staff $manager;

    protected Staff $frontDesk;

    protected function setUp(): void
    {
        parent::setUp();

        $dir = sys_get_temp_dir().'/mtmtp_phpunit_'.getmypid();
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $this->centralPath = $dir.'/central.sqlite';
        $this->tenantPath = $dir.'/lab_local.sqlite';
        @unlink($this->centralPath);
        @unlink($this->tenantPath);

        Config::set('database.default', 'sqlite');
        Config::set('tenancy.central_connection', 'sqlite');
        Config::set('tenancy.connection_name', 'tenant');
        Config::set('tenancy.central_domains', ['saas.localhost']);
        Config::set('tenancy.central_host_pattern', '/^saas\\./');

        Config::set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => $this->centralPath,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        Config::set('database.connections.tenant', [
            'driver' => 'sqlite',
            'database' => $this->tenantPath,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        DB::purge('sqlite');
        DB::purge('tenant');
        DB::reconnect('sqlite');
        DB::reconnect('tenant');

        $this->migrateCentral();
        $this->migrateTenant();
        $this->seedLab();
    }

    protected function tearDown(): void
    {
        DB::purge('sqlite');
        DB::purge('tenant');
        @unlink($this->centralPath);
        @unlink($this->tenantPath);
        parent::tearDown();
    }

    protected function migrateCentral(): void
    {
        Schema::connection('sqlite')->create('saas_tenant', function ($t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('db_name')->unique();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::connection('sqlite')->create('saas_domain', function ($t) {
            $t->id();
            $t->unsignedBigInteger('tenant_id');
            $t->string('hostname')->unique();
            $t->boolean('is_primary')->default(false);
            $t->timestamp('created_at')->nullable();
        });
        Schema::connection('sqlite')->create('saas_user', function ($t) {
            $t->id();
            $t->string('email')->unique();
            $t->string('password');
            $t->boolean('is_active')->default(true);
            $t->timestamp('created_at')->nullable();
        });
    }

    protected function migrateTenant(): void
    {
        Schema::connection('tenant')->create('core_staff', function ($t) {
            $t->id();
            $t->string('email')->unique();
            $t->string('password');
            $t->string('role', 32);
            $t->boolean('is_active')->default(true);
            $t->dateTime('last_login')->nullable();
            $t->dateTime('created_at');
        });
        Schema::connection('tenant')->create('core_building', function ($t) {
            $t->id();
            $t->string('name');
            $t->string('building_type', 32)->default('other');
            $t->integer('altitude_m')->nullable();
            $t->decimal('latitude', 9, 6)->nullable();
            $t->decimal('longitude', 9, 6)->nullable();
            $t->boolean('is_active')->default(true);
            $t->dateTime('created_at');
            $t->dateTime('updated_at');
        });
        Schema::connection('tenant')->create('core_outlet', function ($t) {
            $t->id();
            $t->unsignedBigInteger('building_id')->nullable();
            $t->string('name');
            $t->string('outlet_type', 32)->default('other');
            $t->boolean('is_active')->default(true);
            $t->dateTime('created_at');
        });
        Schema::connection('tenant')->create('core_season', function ($t) {
            $t->id();
            $t->string('kind', 16);
            $t->date('starts_on');
            $t->date('ends_on');
            $t->boolean('is_active')->default(true);
            $t->dateTime('created_at');
            $t->dateTime('updated_at');
        });
        Schema::connection('tenant')->create('core_tenant_settings', function ($t) {
            $t->id();
            $t->string('currency', 8)->default('EUR');
            $t->json('tax_rates')->nullable();
            $t->string('timezone', 64)->default('UTC');
            $t->string('units', 16)->default('metric');
            $t->dateTime('created_at');
            $t->dateTime('updated_at');
        });
        Schema::connection('tenant')->create('core_staff_outlet_access', function ($t) {
            $t->id();
            $t->unsignedBigInteger('staff_id');
            $t->unsignedBigInteger('outlet_id');
            $t->dateTime('created_at');
            $t->unique(['staff_id', 'outlet_id']);
        });
        Schema::connection('tenant')->create('core_audit_event', function ($t) {
            $t->id();
            $t->unsignedBigInteger('actor_staff_id')->nullable();
            $t->string('action', 16);
            $t->string('entity_type', 128);
            $t->unsignedBigInteger('entity_id');
            $t->json('old_values')->nullable();
            $t->json('new_values')->nullable();
            $t->string('reason', 255)->default('');
            $t->string('request_id', 128)->default('');
            $t->string('ip', 45)->nullable();
            $t->dateTime('created_at');
        });
    }

    protected function seedLab(): void
    {
        SaasUser::query()->create([
            'email' => 'super@saas.test',
            'password' => Hash::make('password123'),
            'is_active' => true,
            'created_at' => now(),
        ]);

        // For SQLite harness, db_name is the file path (Postgres uses logical names).
        $this->labTenant = Tenant::query()->create([
            'name' => 'Lab Complex',
            'slug' => 'lab',
            'db_name' => $this->tenantPath,
            'is_active' => true,
        ]);

        Domain::query()->create([
            'tenant_id' => $this->labTenant->getKey(),
            'hostname' => 'lab.localhost',
            'is_primary' => true,
            'created_at' => now(),
        ]);

        TenantConnection::activate($this->tenantPath);

        $this->owner = Staff::on('tenant')->create([
            'email' => 'owner@lab.test',
            'password' => Hash::make('password123'),
            'role' => StaffRole::OWNER,
            'is_active' => true,
            'created_at' => now(),
        ]);
        $this->manager = Staff::on('tenant')->create([
            'email' => 'manager@lab.test',
            'password' => Hash::make('password123'),
            'role' => StaffRole::MANAGER,
            'is_active' => true,
            'created_at' => now(),
        ]);
        $this->frontDesk = Staff::on('tenant')->create([
            'email' => 'desk@lab.test',
            'password' => Hash::make('password123'),
            'role' => StaffRole::FRONT_DESK,
            'is_active' => true,
            'created_at' => now(),
        ]);
    }

    /** Bind Host + activate tenant connection like middleware would. */
    protected function asTenantHost(string $host = 'lab.localhost'): static
    {
        return $this->withServerVariables(['HTTP_HOST' => $host, 'SERVER_NAME' => $host]);
    }

    protected function actingAsStaff(Staff $staff, string $host = 'lab.localhost'): static
    {
        Config::set('database.connections.tenant.database', $this->tenantPath);
        DB::purge('tenant');
        DB::reconnect('tenant');
        app()->instance('tenant', $this->labTenant);
        app()->instance('staff', $staff);

        return $this->asTenantHost($host)->withSession([
            'staff_id' => $staff->getKey(),
            'staff_email' => $staff->email,
            'staff_tenant_id' => $this->labTenant->getKey(),
            'staff_tenant_slug' => $this->labTenant->slug,
        ]);
    }
}
