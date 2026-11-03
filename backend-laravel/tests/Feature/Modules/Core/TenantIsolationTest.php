<?php

namespace Tests\Feature\Modules\Core;

use App\Tenancy\TenantConnection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Modules\Core\App\Enums\StaffRole;
use Modules\Core\App\Models\Building;
use Modules\Core\App\Models\Staff;
use Modules\Saas\App\Models\Domain;
use Modules\Saas\App\Models\Tenant;
use Tests\Support\TenancyTestCase;

/**
 * PDF Module A done-when: two complexes cannot see each other's data.
 */
class TenantIsolationTest extends TenancyTestCase
{
    protected string $demoPath;

    protected Tenant $demoTenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->demoPath = sys_get_temp_dir().'/mtmtp_phpunit_'.getmypid().'/demo_local.sqlite';
        @unlink($this->demoPath);
        touch($this->demoPath);

        Config::set('database.connections.tenant.database', $this->demoPath);
        DB::purge('tenant');
        DB::reconnect('tenant');

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

        $this->demoTenant = Tenant::query()->create([
            'name' => 'Demo Complex',
            'slug' => 'demo',
            'db_name' => $this->demoPath,
            'is_active' => true,
        ]);
        Domain::query()->create([
            'tenant_id' => $this->demoTenant->getKey(),
            'hostname' => 'demo.localhost',
            'is_primary' => true,
            'created_at' => now(),
        ]);

        TenantConnection::activate($this->demoPath);
        Staff::on('tenant')->create([
            'email' => 'owner@demo.test',
            'password' => Hash::make('password123'),
            'role' => StaffRole::OWNER,
            'is_active' => true,
            'created_at' => now(),
        ]);
        Building::on('tenant')->create([
            'name' => 'Demo Only Hut',
            'building_type' => 'mountain_hut',
            'altitude_m' => 2500,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Restore lab tenant connection for lab-host requests.
        TenantConnection::activate($this->tenantPath);
        Building::on('tenant')->create([
            'name' => 'Lab Only Hotel',
            'building_type' => 'main_hotel',
            'altitude_m' => 1200,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function tearDown(): void
    {
        @unlink($this->demoPath);
        parent::tearDown();
    }

    public function test_lab_owner_lists_only_lab_buildings(): void
    {
        $response = $this->actingAsStaff($this->owner)->getJson('/api/buildings');

        $response->assertOk();
        $payload = $response->json('data') ?? $response->json();
        $names = collect($payload)->pluck('name')->filter()->values()->all();
        if ($names === []) {
            // Non-envelope list shapes
            $names = collect($response->json())->pluck('name')->filter()->all();
        }

        $this->assertContains('Lab Only Hotel', $names);
        $this->assertNotContains('Demo Only Hut', $names);
    }

    public function test_cross_host_session_mismatch(): void
    {
        // Lab session cookies against demo host must fail tenant bind.
        $response = $this->actingAsStaff($this->owner, 'lab.localhost')
            ->getJson('http://demo.localhost/api/buildings');

        $this->assertTrue(in_array($response->status(), [401, 403, 404], true));
    }

    public function test_unknown_host_404(): void
    {
        $response = $this->withServerVariables([
            'HTTP_HOST' => 'unknown.localhost',
            'SERVER_NAME' => 'unknown.localhost',
        ])->getJson('/api/auth/me');

        $response->assertStatus(404);
    }
}
