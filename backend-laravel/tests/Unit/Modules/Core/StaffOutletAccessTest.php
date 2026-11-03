<?php

namespace Tests\Unit\Modules\Core;

use Modules\Core\App\Models\Building;
use Modules\Core\App\Models\Outlet;
use Modules\Core\App\Models\StaffOutletAccess;
use Tests\Support\TenancyTestCase;

class StaffOutletAccessTest extends TenancyTestCase
{
    public function test_owner_has_implicit_outlet_access(): void
    {
        $this->assertTrue($this->owner->hasOutletAccess(999));
    }

    public function test_front_desk_needs_explicit_access(): void
    {
        $building = Building::on('tenant')->create([
            'name' => 'Hut',
            'building_type' => 'mountain_hut',
            'altitude_m' => 2100,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $outlet = Outlet::on('tenant')->create([
            'building_id' => $building->getKey(),
            'name' => 'Kitchen',
            'outlet_type' => 'restaurant',
            'is_active' => true,
            'created_at' => now(),
        ]);

        $this->assertFalse($this->frontDesk->hasOutletAccess((int) $outlet->getKey()));

        StaffOutletAccess::on('tenant')->create([
            'staff_id' => $this->frontDesk->getKey(),
            'outlet_id' => $outlet->getKey(),
            'created_at' => now(),
        ]);

        $this->assertTrue($this->frontDesk->fresh()->hasOutletAccess((int) $outlet->getKey()));
    }

    public function test_manager_plus_helpers(): void
    {
        $this->assertTrue($this->owner->isOwner());
        $this->assertTrue($this->owner->isManagerPlus());
        $this->assertTrue($this->manager->isManagerPlus());
        $this->assertFalse($this->frontDesk->isManagerPlus());
        $this->assertTrue($this->owner->canResetPasswords());
        $this->assertTrue($this->manager->canResetPasswords());
        $this->assertFalse($this->frontDesk->canResetPasswords());
    }
}
