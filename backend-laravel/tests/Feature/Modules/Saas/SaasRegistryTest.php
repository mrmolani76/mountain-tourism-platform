<?php

namespace Tests\Feature\Modules\Saas;

use Modules\Saas\App\Models\SaasUser;
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

        $this->assertTrue(Tenant::query()->where('slug', 'demo')->exists());
    }

    public function test_deactivate_tenant(): void
    {
        $id = $this->labTenant->getKey();

        $response = $this->actingAsSaasAdmin()->postJson('/api/saas/tenants/'.$id.'/deactivate');

        $response->assertOk()->assertJsonPath('is_active', false);
        $this->assertFalse((bool) $this->labTenant->fresh()->is_active);
    }

    public function test_activate_tenant(): void
    {
        $this->labTenant->is_active = false;
        $this->labTenant->save();
        $id = $this->labTenant->getKey();

        $response = $this->actingAsSaasAdmin()->postJson('/api/saas/tenants/'.$id.'/activate');

        $response->assertOk()->assertJsonPath('is_active', true);
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
