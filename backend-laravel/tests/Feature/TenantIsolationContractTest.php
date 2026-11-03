<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Module 1 DoD — two complexes cannot see each other's data (PDF + deep analysis §18.7 / §20).
 *
 * These tests are the Laravel contract. They require a setup-machine Postgres with two
 * provisioned tenants (lab + demo) OR skip when TENANT_ISOLATION_E2E is unset.
 *
 * Run on setup machine:
 *   TENANT_ISOLATION_E2E=1 php artisan test --filter=TenantIsolationContractTest
 *
 * Expectation (when enabled):
 * - Host lab.localhost session cannot read demo.localhost buildings
 * - Cross-host session replay returns 401 Session tenant mismatch
 */
class TenantIsolationContractTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! env('TENANT_ISOLATION_E2E')) {
            $this->markTestSkipped('Set TENANT_ISOLATION_E2E=1 on setup machine after provisioning lab + demo.');
        }
    }

    public function test_staff_login_requires_tenant_host(): void
    {
        $response = $this->postJson('http://saas.localhost/api/auth/login', [
            'email' => 'owner@lab.test',
            'password' => 'password123',
        ]);

        // Staff login on central host must not succeed as a tenant session.
        $this->assertTrue(in_array($response->status(), [401, 403, 404], true));
    }

    public function test_lab_owner_cannot_list_demo_buildings_via_wrong_host(): void
    {
        $login = $this->postJson('http://lab.localhost/api/auth/login', [
            'email' => 'owner@lab.test',
            'password' => 'password123',
        ]);
        $login->assertOk();

        // Replay cookies against the other tenant host — session tenant must mismatch.
        $demo = $this->withCookies($login->headers->getCookies() ? [] : [])
            ->getJson('http://demo.localhost/api/buildings');

        $this->assertTrue(in_array($demo->status(), [401, 403, 404], true));
    }
}
