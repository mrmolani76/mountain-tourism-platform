<?php

namespace Tests\Feature\Modules\Saas;

use Illuminate\Support\Facades\Schema;
use Modules\Core\App\Enums\StaffRole;
use Modules\Core\App\Models\Staff;
use Modules\Saas\App\Models\SaasUser;
use Modules\Saas\App\Models\SaasTenantActivity;
use Modules\Saas\App\Models\Tenant;
use Tests\Support\TenancyTestCase;

/**
 * Module Saas — central registry (Django test_saas_registry.py parity).
 */
class SaasRegistryTest extends TenancyTestCase
{
    protected function actingAsSaasAdmin(): static
    {
        $admin = SaasUser::query()->where('email', 'super@saas.test')->firstOrFail();

        return $this->withServerVariables([
            'HTTP_HOST' => 'saas.localhost',
            'SERVER_NAME' => 'saas.localhost',
        ])->withSession([
            'saas_user_id' => $admin->getKey(),
            'saas_user_email' => $admin->email,
        ]);
    }

    public function test_saas_login(): void
    {
        $response = $this->withServerVariables([
            'HTTP_HOST' => 'saas.localhost',
            'SERVER_NAME' => 'saas.localhost',
        ])->postJson('/api/saas/auth/login', [
            'email' => 'super@saas.test',
            'password' => 'password123',
        ]);

        $response->assertOk();
    }

    public function test_register_tenant_registry(): void
    {
        $response = $this->actingAsSaasAdmin()->postJson('/api/saas/tenants', [
            'name' => 'Demo Complex',
            'slug' => 'demo',
            'base_domain' => 'localhost',
        ]);

        $response->assertCreated()
            ->assertJsonPath('slug', 'demo')
            ->assertJsonPath('owner_created', false);

        $this->assertIsString($response->json('uuid'));
        $this->assertNotEmpty($response->json('uuid'));
        $this->assertTrue(Tenant::query()->where('slug', 'demo')->exists());

        $tenant = Tenant::query()->where('slug', 'demo')->firstOrFail();
        $activity = SaasTenantActivity::query()->where('tenant_id', $tenant->getKey())->firstOrFail();
        $this->assertSame('tenant_created', $activity->event_type);
        $this->assertFalse($activity->metadata['owner_created']);
        $this->assertArrayNotHasKey('owner_temporary_password', $activity->metadata);
    }

    public function test_deactivate_tenant(): void
    {
        $uuid = $this->labTenant->uuid;

        $response = $this->actingAsSaasAdmin()->postJson('/api/saas/tenants/'.$uuid.'/deactivate');

        $response->assertOk()->assertJsonPath('is_active', false);
        $this->assertFalse((bool) $this->labTenant->fresh()->is_active);
    }

    public function test_activate_tenant(): void
    {
        $this->labTenant->is_active = false;
        $this->labTenant->save();
        $uuid = $this->labTenant->uuid;

        $response = $this->actingAsSaasAdmin()->postJson('/api/saas/tenants/'.$uuid.'/activate');

        $response->assertOk()->assertJsonPath('is_active', true);
    }

    public function test_tenant_list_filters_by_search_and_status(): void
    {
        Tenant::query()->create([
            'name' => 'Inactive Complex',
            'slug' => 'inactive',
            'db_name' => 'inactive_local',
            'is_active' => false,
        ]);

        $response = $this->actingAsSaasAdmin()->getJson('/api/saas/tenants?search=Lab&status=active&page=1&page_size=10');

        $response->assertOk()
            ->assertJsonPath('meta.total_count', 1)
            ->assertJsonPath('data.0.slug', 'lab');
    }

    public function test_saas_dashboard_counts_active_and_inactive_tenants(): void
    {
        $this->labTenant->is_active = false;
        $this->labTenant->save();
        Tenant::query()->create([
            'name' => 'Active Complex',
            'slug' => 'active',
            'db_name' => 'active_local',
            'is_active' => true,
        ]);

        $response = $this->actingAsSaasAdmin()->getJson('/api/saas/dashboard');

        $response->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('active', 1)
            ->assertJsonPath('inactive', 1);
    }

    public function test_tenant_readiness_checks_core_schema_and_owner(): void
    {
        $response = $this->actingAsSaasAdmin()->getJson('/api/saas/tenants/'.$this->labTenant->uuid.'/readiness');

        $response->assertOk()
            ->assertJsonPath('tenant_database.ready', true)
            ->assertJsonPath('tenant_migrations.ready', true)
            ->assertJsonPath('owner_account.ready', true)
            ->assertJsonPath('ready', true);
    }

    public function test_owner_can_be_created_after_tenant_provisioning(): void
    {
        $this->owner->delete();

        $response = $this->actingAsSaasAdmin()->postJson(
            '/api/saas/tenants/'.$this->labTenant->uuid.'/owner',
            [
                'owner_email' => 'new-owner@lab.test',
            ]
        );

        $response->assertCreated()
            ->assertJsonPath('owner_created', true)
            ->assertJsonMissingPath('id');

        $temporaryPassword = $response->json('owner_temporary_password');
        $this->assertIsString($temporaryPassword);
        $this->assertSame(16, strlen($temporaryPassword));

        $newOwner = Staff::on('tenant')->where('email', 'new-owner@lab.test')->firstOrFail();
        $this->assertSame(StaffRole::OWNER, $newOwner->role);
        $this->assertTrue($newOwner->is_active);
        $this->assertTrue($newOwner->force_reset_password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check($temporaryPassword, $newOwner->password));
        $this->assertStringNotContainsString($newOwner->password, $response->getContent());
        $activity = SaasTenantActivity::query()->where('event_type', 'owner_created')->firstOrFail();
        $this->assertSame($this->labTenant->getKey(), $activity->tenant_id);
        $this->assertTrue($activity->metadata['temporary_password_generated']);
        $this->assertTrue($activity->metadata['force_reset_password']);
        $this->assertArrayNotHasKey('owner_temporary_password', $activity->metadata);
        $this->assertStringNotContainsString($temporaryPassword, json_encode($activity->metadata));
    }

    public function test_owner_setup_does_not_promote_or_reset_existing_staff(): void
    {
        $originalPassword = $this->manager->password;

        $response = $this->actingAsSaasAdmin()->postJson(
            '/api/saas/tenants/'.$this->labTenant->uuid.'/owner',
            [
                'owner_email' => 'MANAGER@LAB.TEST',
            ]
        );

        $response->assertStatus(409)
            ->assertJsonPath('owner_created', false);

        $manager = $this->manager->fresh();
        $this->assertSame(StaffRole::MANAGER, $manager->role);
        $this->assertSame($originalPassword, $manager->password);
    }

    public function test_owner_setup_returns_conflict_until_core_staff_table_exists(): void
    {
        Schema::connection('tenant')->drop('core_staff');

        $response = $this->actingAsSaasAdmin()->postJson(
            '/api/saas/tenants/'.$this->labTenant->uuid.'/owner',
            [
                'owner_email' => 'new-owner@lab.test',
            ]
        );

        $response->assertStatus(409)
            ->assertJsonPath('owner_created', false);
    }

    public function test_tenant_lifecycle_changes_are_recorded_once_per_transition(): void
    {
        $id = $this->labTenant->getKey();
        $uuid = $this->labTenant->uuid;

        $this->actingAsSaasAdmin()->postJson('/api/saas/tenants/'.$uuid.'/deactivate')->assertOk();
        $this->actingAsSaasAdmin()->postJson('/api/saas/tenants/'.$uuid.'/deactivate')->assertOk();
        $this->actingAsSaasAdmin()->postJson('/api/saas/tenants/'.$uuid.'/activate')->assertOk();

        $this->assertDatabaseCount('saas_tenant_activity', 2, 'sqlite');
        $this->assertDatabaseHas('saas_tenant_activity', [
            'tenant_id' => $id,
            'event_type' => 'tenant_deactivated',
        ], 'sqlite');
        $this->assertDatabaseHas('saas_tenant_activity', [
            'tenant_id' => $id,
            'event_type' => 'tenant_activated',
        ], 'sqlite');

        $dashboard = $this->actingAsSaasAdmin()->getJson('/api/saas/dashboard');
        $dashboard->assertOk()
            ->assertJsonPath('recent_activity.0.event_type', 'tenant_activated')
            ->assertJsonPath('recent_activity.0.tenant_name', 'Lab Complex');
    }

    public function test_status_changes_via_update_are_recorded(): void
    {
        $id = $this->labTenant->getKey();
        $uuid = $this->labTenant->uuid;

        $response = $this->actingAsSaasAdmin()
            ->patchJson('/api/saas/tenants/'.$uuid, ['is_active' => false]);

        $response->assertOk()->assertJsonPath('is_active', false);
        $this->assertDatabaseHas('saas_tenant_activity', [
            'tenant_id' => $id,
            'event_type' => 'tenant_deactivated',
        ], 'sqlite');
    }

    public function test_tenant_name_changes_are_recorded_without_sensitive_data(): void
    {
        $uuid = $this->labTenant->uuid;

        $response = $this->actingAsSaasAdmin()
            ->patchJson('/api/saas/tenants/'.$uuid, ['name' => 'Updated Lab Complex']);

        $response->assertOk()->assertJsonPath('name', 'Updated Lab Complex');
        $activity = SaasTenantActivity::query()
            ->where('tenant_id', $this->labTenant->getKey())
            ->where('event_type', 'tenant_updated')
            ->firstOrFail();

        $this->assertSame(['name'], $activity->metadata['updated_fields']);
        $this->assertStringNotContainsString('password', strtolower(json_encode($activity->metadata)));
    }

    public function test_tenant_fetch_uses_uuid_not_the_numeric_primary_key(): void
    {
        $response = $this->actingAsSaasAdmin()
            ->getJson('/api/saas/tenants/'.$this->labTenant->uuid);

        $response->assertOk()
            ->assertJsonPath('uuid', $this->labTenant->uuid)
            ->assertJsonMissingPath('id');

        $this->actingAsSaasAdmin()
            ->getJson('/api/saas/tenants/'.$this->labTenant->getKey())
            ->assertNotFound();
    }

    public function test_saas_me_requires_session(): void
    {
        $response = $this->withServerVariables([
            'HTTP_HOST' => 'saas.localhost',
            'SERVER_NAME' => 'saas.localhost',
        ])->getJson('/api/saas/auth/me');

        $this->assertTrue(in_array($response->status(), [401, 403], true));
    }
}
