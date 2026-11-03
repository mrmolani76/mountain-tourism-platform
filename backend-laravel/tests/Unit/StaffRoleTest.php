<?php

namespace Tests\Unit;

use Modules\Core\App\Enums\StaffRole;
use PHPUnit\Framework\TestCase;

/**
 * PDF §3 roles — no DB required.
 */
class StaffRoleTest extends TestCase
{
    public function test_all_mvp_role_codes_present(): void
    {
        $expected = [
            'owner', 'manager', 'front_desk', 'cashier',
            'rental_desk', 'service_staff', 'transport_desk', 'lift_operator',
        ];

        $this->assertSame($expected, StaffRole::codes());
    }

    public function test_owner_and_manager_can_reset_passwords(): void
    {
        $this->assertTrue(StaffRole::canResetPasswords(StaffRole::OWNER));
        $this->assertTrue(StaffRole::canResetPasswords(StaffRole::MANAGER));
        $this->assertFalse(StaffRole::canResetPasswords(StaffRole::FRONT_DESK));
        $this->assertFalse(StaffRole::canResetPasswords(StaffRole::CASHIER));
    }

    public function test_driver_is_not_a_login_role(): void
    {
        $this->assertFalse(StaffRole::isValid('driver'));
        $this->assertNotContains('driver', StaffRole::codes());
    }
}
