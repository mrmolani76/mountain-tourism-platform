<?php

namespace Tests\Feature\Modules\Core;

use Modules\Core\App\Models\Building;
use Modules\Core\App\Models\Outlet;
use Modules\Core\App\Models\Season;
use Modules\Core\App\Models\StaffOutletAccess;
use Tests\Support\TenancyTestCase;

/**
 * Module Core — buildings / outlets / seasons (Django test_core_config.py parity).
 */
class BuildingOutletSeasonTest extends TenancyTestCase
{
    public function test_building_create_and_list_by_owner(): void
    {
        $create = $this->actingAsStaff($this->owner)->postJson('/api/buildings', [
            'name' => 'Main Hotel',
            'building_type' => 'main_hotel',
            'altitude_m' => 1200,
            'latitude' => 46.85,
            'longitude' => 9.52,
            'is_active' => true,
        ]);

        $create->assertCreated();

        $list = $this->actingAsStaff($this->owner)->getJson('/api/buildings');
        $list->assertOk();
        $this->assertGreaterThanOrEqual(1, count($list->json('data') ?? $list->json()));
    }

    public function test_buildings_denied_for_manager(): void
    {
        $response = $this->actingAsStaff($this->manager)->getJson('/api/buildings');

        $response->assertStatus(403);
    }

    public function test_outlet_crud_by_owner(): void
    {
        $building = Building::on('tenant')->create([
            'name' => 'Valley',
            'building_type' => 'valley_station',
            'altitude_m' => 1050,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $create = $this->actingAsStaff($this->owner)->postJson('/api/outlets', [
            'building_id' => $building->getKey(),
            'name' => 'Rental Desk',
            'outlet_type' => 'rental',
            'is_active' => true,
        ]);

        $create->assertCreated()->assertJsonPath('name', 'Rental Desk');
        $id = $create->json('id');

        $show = $this->actingAsStaff($this->owner)->getJson('/api/outlets/'.$id);
        $show->assertOk();
    }

    public function test_outlet_show_denied_without_access(): void
    {
        $building = Building::on('tenant')->create([
            'name' => 'Valley',
            'building_type' => 'valley_station',
            'altitude_m' => 1050,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $outlet = Outlet::on('tenant')->create([
            'building_id' => $building->getKey(),
            'name' => 'Shop',
            'outlet_type' => 'shop',
            'is_active' => true,
            'created_at' => now(),
        ]);

        $response = $this->actingAsStaff($this->frontDesk)->getJson('/api/outlets/'.$outlet->getKey());

        $response->assertStatus(403);
    }

    public function test_outlet_show_allowed_with_access(): void
    {
        $building = Building::on('tenant')->create([
            'name' => 'Valley',
            'building_type' => 'valley_station',
            'altitude_m' => 1050,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $outlet = Outlet::on('tenant')->create([
            'building_id' => $building->getKey(),
            'name' => 'Shop',
            'outlet_type' => 'shop',
            'is_active' => true,
            'created_at' => now(),
        ]);
        StaffOutletAccess::on('tenant')->create([
            'staff_id' => $this->frontDesk->getKey(),
            'outlet_id' => $outlet->getKey(),
            'created_at' => now(),
        ]);

        $response = $this->actingAsStaff($this->frontDesk)->getJson('/api/outlets/'.$outlet->getKey());

        $response->assertOk()->assertJsonPath('name', 'Shop');
    }

    public function test_season_create_by_owner(): void
    {
        $response = $this->actingAsStaff($this->owner)->postJson('/api/seasons', [
            'kind' => 'winter',
            'starts_on' => '2026-12-01',
            'ends_on' => '2027-03-31',
            'is_active' => true,
        ]);

        $response->assertCreated();
    }

    public function test_season_overlap_rejected(): void
    {
        Season::on('tenant')->create([
            'kind' => 'winter',
            'starts_on' => '2026-12-01',
            'ends_on' => '2027-03-31',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAsStaff($this->owner)->postJson('/api/seasons', [
            'kind' => 'winter',
            'starts_on' => '2027-01-01',
            'ends_on' => '2027-02-28',
            'is_active' => true,
        ]);

        $response->assertStatus(400);
    }

    public function test_settings_get_by_owner(): void
    {
        $response = $this->actingAsStaff($this->owner)->getJson('/api/settings');

        $response->assertOk()
            ->assertJsonStructure(['currency', 'timezone', 'units', 'tax_rates']);
    }

    public function test_settings_denied_for_manager(): void
    {
        $response = $this->actingAsStaff($this->manager)->getJson('/api/settings');

        $response->assertStatus(403);
    }

    public function test_staff_list_denied_for_front_desk(): void
    {
        $response = $this->actingAsStaff($this->frontDesk)->getJson('/api/staff');

        $response->assertStatus(403);
    }

    public function test_staff_crud_by_owner(): void
    {
        $response = $this->actingAsStaff($this->owner)->postJson('/api/staff', [
            'email' => 'cashier@lab.test',
            'password' => 'password123',
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $response->assertCreated()->assertJsonPath('role', 'cashier');
    }
}
