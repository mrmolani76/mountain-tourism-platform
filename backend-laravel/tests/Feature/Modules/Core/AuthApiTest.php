<?php

namespace Tests\Feature\Modules\Core;

use Modules\Core\App\Models\Staff;
use Tests\Support\TenancyTestCase;

/**
 * Module Core — staff auth (Django test_auth.py parity).
 */
class AuthApiTest extends TenancyTestCase
{
    public function test_login_success(): void
    {
        $response = $this->asTenantHost()->postJson('/api/auth/login', [
            'email' => 'owner@lab.test',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('email', 'owner@lab.test')
            ->assertJsonPath('role_code', 'owner')
            ->assertJsonPath('tenant_slug', 'lab');
    }

    public function test_login_wrong_password(): void
    {
        $response = $this->asTenantHost()->postJson('/api/auth/login', [
            'email' => 'owner@lab.test',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_unknown_email(): void
    {
        $response = $this->asTenantHost()->postJson('/api/auth/login', [
            'email' => 'nobody@lab.test',
            'password' => 'password123',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_inactive_staff_denied(): void
    {
        Staff::on('tenant')->where('email', 'desk@lab.test')->update(['is_active' => false]);

        $response = $this->asTenantHost()->postJson('/api/auth/login', [
            'email' => 'desk@lab.test',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
    }

    public function test_me_returns_staff(): void
    {
        $response = $this->actingAsStaff($this->owner)->getJson('/api/auth/me');

        $response->assertOk()
            ->assertJsonPath('email', 'owner@lab.test')
            ->assertJsonPath('role_code', 'owner');
    }

    public function test_me_without_auth_denied(): void
    {
        $response = $this->asTenantHost()->getJson('/api/auth/me');

        $response->assertStatus(401);
    }

    public function test_logout(): void
    {
        $response = $this->actingAsStaff($this->owner)->postJson('/api/auth/logout');

        $response->assertOk()->assertJsonPath('detail', 'Logged out.');
    }

    public function test_password_reset_by_owner(): void
    {
        $response = $this->actingAsStaff($this->owner)->postJson('/api/auth/reset-password', [
            'staff_id' => $this->frontDesk->getKey(),
            'new_password' => 'newpass123',
        ]);

        $response->assertOk()->assertJsonPath('detail', 'Password reset.');
    }

    public function test_password_reset_by_manager(): void
    {
        $response = $this->actingAsStaff($this->manager)->postJson('/api/auth/reset-password', [
            'staff_id' => $this->frontDesk->getKey(),
            'new_password' => 'newpass123',
        ]);

        $response->assertOk();
    }

    public function test_password_reset_denied_for_front_desk(): void
    {
        $response = $this->actingAsStaff($this->frontDesk)->postJson('/api/auth/reset-password', [
            'staff_id' => $this->manager->getKey(),
            'new_password' => 'newpass123',
        ]);

        $response->assertStatus(403);
    }

    public function test_staff_session_rejected_by_saas_endpoint(): void
    {
        $response = $this->actingAsStaff($this->owner)
            ->withServerVariables(['HTTP_HOST' => 'saas.localhost', 'SERVER_NAME' => 'saas.localhost'])
            ->getJson('/api/saas/auth/me');

        $this->assertTrue(in_array($response->status(), [401, 403], true));
    }
}
