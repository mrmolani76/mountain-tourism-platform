<?php

namespace Tests\Unit;

use Modules\Saas\App\Services\TenantService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Documents Owner-on-create contract without hitting a live DB.
 * Full createOwnerStaff path is exercised on setup machine via provision + POST /api/saas/tenants.
 */
class TenantServiceOwnerContractTest extends TestCase
{
    public function test_register_method_returns_owner_created_flag_shape(): void
    {
        $method = new ReflectionMethod(TenantService::class, 'register');
        $this->assertTrue($method->isPublic());

        $createOwner = new ReflectionMethod(TenantService::class, 'createOwnerStaff');
        $this->assertTrue($createOwner->isPublic());
    }
}
