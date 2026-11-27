<?php

namespace Tests\Feature\Modules\Hotel;

use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Modules\Core\App\Models\Building;
use Modules\Core\App\Models\Outlet;
use Modules\Core\App\Models\Season;
use Modules\Folio\App\Models\Charge;
use Modules\Folio\App\Models\Folio;
use Modules\Folio\App\Models\FolioVoid;
use Modules\Folio\App\Models\Payment;
use Modules\Folio\App\Services\ChargeToRoomService;
use Modules\Hotel\App\Models\BoardPlan;
use Modules\Hotel\App\Models\HutBed;
use Modules\Hotel\App\Models\HutDormitory;
use Modules\Hotel\App\Models\NightlyPosting;
use Modules\Hotel\App\Models\Reservation;
use Modules\Hotel\App\Models\ReservationAccommodation;
use Modules\Hotel\App\Models\Room;
use Modules\Hotel\App\Models\RoomType;
use Modules\Hotel\App\Models\Stay;
use Modules\Hotel\App\Data\AvailabilityData;
use Modules\Hotel\App\Services\NightlyPostingService;
use Modules\Services\App\Models\Booking;
use Modules\Services\App\Models\ServiceDefinition;
use Modules\Services\App\Models\Slot;
use Modules\Weather\App\Models\WeatherPoint;
use Modules\Weather\App\Models\WeatherWarning;
use Tests\Support\MigratesModuleSchema;
use Tests\Support\TenancyTestCase;

/**
 * Module 2 — Hotel, chalets and huts + minimal folio contract.
 * Covers inventory, bed-level hut sales, overlap guard, seasonal pricing,
 * check-in folio, nightly posting idempotency and the checkout zero-balance gate.
 */
class HotelModuleTest extends TenancyTestCase
{
    use MigratesModuleSchema;

    protected function migrateTenant(): void
    {
        parent::migrateTenant();

        $this->migrateModuleTenantSchema(
            'Modules/Folio/Database/migrations/tenant/2026_11_04_000001_create_folio_folio_table.php',
            'Modules/Folio/Database/migrations/tenant/2026_11_04_000002_create_folio_charge_table.php',
            'Modules/Folio/Database/migrations/tenant/2026_11_04_000003_create_folio_payment_table.php',
            'Modules/Folio/Database/migrations/tenant/2026_11_04_000004_create_folio_void_table.php',
            'Modules/Folio/Database/migrations/tenant/2026_11_27_000001_protect_folio_ledger_foreign_keys.php',
            'Modules/Hotel/Database/migrations/tenant/2026_11_04_000001_create_hotel_room_type_table.php',
            'Modules/Hotel/Database/migrations/tenant/2026_11_04_000002_create_hotel_room_table.php',
            'Modules/Hotel/Database/migrations/tenant/2026_11_04_000003_create_hotel_hut_dormitory_table.php',
            'Modules/Hotel/Database/migrations/tenant/2026_11_04_000004_create_hotel_hut_bed_table.php',
            'Modules/Hotel/Database/migrations/tenant/2026_11_04_000005_create_hotel_room_season_price_table.php',
            'Modules/Hotel/Database/migrations/tenant/2026_11_04_000006_create_hotel_board_plan_table.php',
            'Modules/Hotel/Database/migrations/tenant/2026_11_04_000007_create_hotel_reservation_table.php',
            'Modules/Hotel/Database/migrations/tenant/2026_11_04_000008_create_hotel_reservation_accommodation_table.php',
            'Modules/Hotel/Database/migrations/tenant/2026_11_04_000009_create_hotel_stay_table.php',
            'Modules/Hotel/Database/migrations/tenant/2026_11_04_000010_create_hotel_nightly_posting_table.php',
            'Modules/Weather/Database/migrations/tenant/2026_11_14_000001_create_weather_point_table.php',
            'Modules/Weather/Database/migrations/tenant/2026_11_14_000002_create_weather_fetch_run_table.php',
            'Modules/Weather/Database/migrations/tenant/2026_11_14_000003_create_weather_forecast_snapshot_table.php',
            'Modules/Weather/Database/migrations/tenant/2026_11_14_000004_create_weather_forecast_value_table.php',
            'Modules/Weather/Database/migrations/tenant/2026_11_14_000005_create_weather_limit_table.php',
            'Modules/Weather/Database/migrations/tenant/2026_11_14_000006_create_weather_warning_table.php',
            'Modules/Weather/Database/migrations/tenant/2026_11_14_000007_create_weather_warning_target_table.php',
            'Modules/Weather/Database/migrations/tenant/2026_11_14_000008_create_weather_cancellation_table.php',
            'Modules/Services/Database/migrations/tenant/2026_11_13_000001_create_service_service_table.php',
            'Modules/Services/Database/migrations/tenant/2026_11_13_000002_create_service_staff_requirement_table.php',
            'Modules/Services/Database/migrations/tenant/2026_11_13_000003_create_service_schedule_rule_table.php',
            'Modules/Services/Database/migrations/tenant/2026_11_13_000004_create_service_slot_table.php',
            'Modules/Services/Database/migrations/tenant/2026_11_13_000005_create_service_slot_staff_table.php',
            'Modules/Services/Database/migrations/tenant/2026_11_13_000006_create_service_booking_table.php',
            'Modules/Services/Database/migrations/tenant/2026_11_13_000007_create_service_booking_participant_table.php',
        );
    }

    public function test_owner_manages_room_types_rooms_and_chalets(): void
    {
        $building = $this->building();

        $type = $this->actingAsStaff($this->owner)->postJson('/api/hotel/room-types', [
            'name' => 'Deluxe',
            'max_adults' => 3,
            'max_children' => 2,
        ]);
        $type->assertCreated()->assertJsonPath('name', 'Deluxe');
        $typeId = $type->json('id');

        $room = $this->actingAsStaff($this->owner)->postJson('/api/hotel/rooms', [
            'building_id' => $building->getKey(),
            'room_type_id' => $typeId,
            'room_number' => '101',
            'accommodation_type' => 'room',
        ]);
        $room->assertCreated()->assertJsonPath('accommodation_type', 'room');

        $chalet = $this->actingAsStaff($this->owner)->postJson('/api/hotel/rooms', [
            'building_id' => $building->getKey(),
            'room_type_id' => $typeId,
            'room_number' => 'C1',
            'name' => 'Family Chalet',
            'accommodation_type' => 'chalet',
        ]);
        $chalet->assertCreated()->assertJsonPath('accommodation_type', 'chalet');

        $list = $this->actingAsStaff($this->owner)
            ->getJson('/api/hotel/rooms?building_id='.$building->getKey());
        $list->assertOk();
        $this->assertCount(2, $list->json('data'));

        $this->actingAsStaff($this->manager)->postJson('/api/hotel/rooms', [
            'building_id' => $building->getKey(),
            'room_type_id' => $typeId,
            'room_number' => '102',
            'accommodation_type' => 'room',
        ])->assertStatus(403);
    }

    public function test_hotel_exists_rules_use_only_the_active_tenant_connection(): void
    {
        $otherTenantPath = $this->tenantPath.'_other.sqlite';
        touch($otherTenantPath);

        Config::set('database.connections.tenant_other', [
            'driver' => 'sqlite',
            'database' => $otherTenantPath,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        DB::purge('tenant_other');

        try {
            Schema::connection('tenant_other')->create('core_building', function ($table): void {
                $table->id();
            });
            DB::connection('tenant_other')->table('core_building')->insert(['id' => 9001]);

            $building = $this->building();
            Config::set('tenancy.connection_name', 'tenant_other');
            $buildingRule = AvailabilityData::rules()['building_id'];

            $activeTenantValue = Validator::make(
                ['building_id' => 9001],
                ['building_id' => $buildingRule],
            );
            $otherTenantValue = Validator::make(
                ['building_id' => $building->getKey()],
                ['building_id' => $buildingRule],
            );

            $this->assertFalse($activeTenantValue->fails());
            $this->assertTrue($otherTenantValue->fails());
        } finally {
            Config::set('tenancy.connection_name', 'tenant');
            DB::purge('tenant_other');
            Config::set('database.connections.tenant_other', null);
            @unlink($otherTenantPath);
        }
    }

    public function test_room_season_price_exists_and_unique_rules_work_over_http(): void
    {
        $roomType = $this->roomType();
        $season = $this->season();
        $payload = [
            'room_type_id' => $roomType->getKey(),
            'season_id' => $season->getKey(),
            'amount_cents' => 12000,
        ];

        $created = $this->actingAsStaff($this->owner)
            ->postJson('/api/hotel/room-season-prices', $payload)
            ->assertCreated();

        $this->actingAsStaff($this->owner)
            ->postJson('/api/hotel/room-season-prices', $payload)
            ->assertStatus(400)
            ->assertJsonStructure(['error' => ['details' => ['season_id']]]);

        $this->actingAsStaff($this->owner)
            ->patchJson('/api/hotel/room-season-prices/'.$created->json('id'), [
                'season_id' => $season->getKey(),
            ])
            ->assertOk();
    }

    public function test_weather_cancellation_http_flow_voids_linked_charge(): void
    {
        $building = $this->building();
        $outlet = Outlet::on('tenant')->where('building_id', $building->getKey())
            ->where('outlet_type', 'reception')->firstOrFail();
        $point = WeatherPoint::on('tenant')->create([
            'building_id' => $building->getKey(),
            'name' => 'Main Hotel',
            'latitude' => 46.5,
            'longitude' => 7.8,
            'altitude_m' => 1200,
            'is_active' => true,
        ]);
        $warning = WeatherWarning::on('tenant')->create([
            'weather_point_id' => $point->getKey(),
            'variable' => 'wind_speed',
            'threshold' => 80,
            'forecast_value' => 100,
            'starts_at' => now(),
            'ends_at' => now()->addHour(),
            'status' => WeatherWarning::STATUS_ACTIVE,
            'raised_at' => now(),
        ]);
        $service = ServiceDefinition::on('tenant')->create([
            'name' => 'Guided hike',
            'service_type' => 'guided_outing',
            'duration_minutes' => 60,
            'default_capacity' => 8,
            'price_cents' => 2500,
            'price_mode' => ServiceDefinition::PRICE_PER_PERSON,
            'outdoor' => true,
            'is_active' => true,
        ]);
        $slot = Slot::on('tenant')->create([
            'service_id' => $service->getKey(),
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
            'capacity' => 8,
            'booked_count' => 1,
            'status' => Slot::STATUS_SCHEDULED,
        ]);
        $folio = $this->openFolioForVoid();
        $charge = Charge::on('tenant')->create([
            'folio_id' => $folio->getKey(),
            'outlet_id' => $outlet->getKey(),
            'posted_on' => now()->toDateString(),
            'description' => 'Guided hike booking',
            'amount_cents' => 2500,
            'tax_cents' => 0,
            'source_type' => Charge::SOURCE_SERVICE,
            'source_id' => (string) $slot->getKey(),
            'is_voided' => false,
        ]);
        Booking::on('tenant')->create([
            'slot_id' => $slot->getKey(),
            'folio_id' => $folio->getKey(),
            'guest_name' => 'Weather Guest',
            'participants_count' => 1,
            'status' => Booking::STATUS_BOOKED,
            'charge_id' => $charge->getKey(),
        ]);

        $response = $this->actingAsStaff($this->manager)
            ->postJson('/api/weather/cancellations', [
                'warning_id' => $warning->getKey(),
                'target_type' => 'service_slot',
                'target_id' => $slot->getKey(),
                'reason' => 'Unsafe wind conditions',
            ]);

        $response->assertCreated()
            ->assertJsonPath('cancellation.warning_id', (int) $warning->getKey())
            ->assertJsonPath('voids.0.reason_code', FolioVoid::REASON_WEATHER);
        $this->assertTrue((bool) Charge::on('tenant')->findOrFail($charge->getKey())->getAttribute('is_voided'));
        $this->assertSame(
            (int) $warning->getKey(),
            (int) Slot::on('tenant')->findOrFail($slot->getKey())->getAttribute('weather_warning_id'),
        );
    }

    public function test_hut_dormitory_sells_individual_beds(): void
    {
        $season = $this->season();
        $type = $this->roomType(['name' => 'Dorm Bed']);
        $this->price($type, $season, 4000);

        $dorm = HutDormitory::on('tenant')->create([
            'building_id' => $this->building()->getKey(),
            'room_type_id' => $type->getKey(),
            'name' => 'Dormitory A',
            'is_active' => true,
        ]);

        $bedOne = $this->bed($dorm, '01');
        $bedTwo = $this->bed($dorm, '02');

        $this->reserveBed($bedOne, '2027-01-10', '2027-01-12')->assertCreated();

        $this->reserveBed($bedOne, '2027-01-11', '2027-01-13')
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'accommodation_unavailable');

        $this->reserveBed($bedTwo, '2027-01-11', '2027-01-13')->assertCreated();

        // Check-out day frees the bed for the next guest.
        $this->reserveBed($bedOne, '2027-01-12', '2027-01-14')->assertCreated();
    }

    public function test_room_overlap_is_rejected_but_back_to_back_is_allowed(): void
    {
        $season = $this->season();
        $type = $this->roomType();
        $this->price($type, $season, 12000);
        $room = $this->room($type);

        $first = $this->reserveRoom($room, '2027-01-10', '2027-01-12');
        $first->assertCreated();

        $this->reserveRoom($room, '2027-01-11', '2027-01-13')
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'accommodation_unavailable');

        $this->reserveRoom($room, '2027-01-12', '2027-01-14')->assertCreated();
    }

    public function test_quote_resolves_seasonal_room_price_per_night(): void
    {
        $season = $this->season();
        $type = $this->roomType();
        $this->price($type, $season, 15000);

        $response = $this->actingAsStaff($this->frontDesk)->getJson(
            '/api/hotel/quote?room_type_id='.$type->getKey()
            .'&check_in_on=2027-01-10&check_out_on=2027-01-13&adults=2'
        );

        $response->assertOk()
            ->assertJsonPath('data.nights', 3)
            ->assertJsonPath('data.room_total_cents', 45000);
    }

    public function test_reservation_stores_arrival_details_and_allocation(): void
    {
        $season = $this->season();
        $type = $this->roomType();
        $this->price($type, $season, 15000);
        $room = $this->room($type);
        $board = $this->boardPlan(BoardPlan::HALF_BOARD, 5000);

        $response = $this->reserveRoom($room, '2027-01-10', '2027-01-13', [
            'board_plan_id' => $board->getKey(),
            'deposit_cents' => 20000,
            'arrival_mode' => 'train',
            'arrival_time' => '14:30',
            'arrival_station' => 'Valley Station',
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', 'pending')
            ->assertJsonPath('arrival_mode', 'train')
            ->assertJsonPath('arrival_time', '14:30')
            ->assertJsonPath('arrival_station', 'Valley Station')
            ->assertJsonPath('deposit_cents', 20000)
            ->assertJsonPath('nights', 3)
            ->assertJsonCount(1, 'allocations');
    }

    public function test_check_in_opens_stay_and_folio(): void
    {
        [$reservation] = $this->threeNightHalfBoardReservation();

        $response = $this->actingAsStaff($this->frontDesk)
            ->postJson('/api/hotel/reservations/'.$reservation->getKey().'/check-in');

        $response->assertCreated()
            ->assertJsonPath('stay.status', Stay::STATUS_IN_HOUSE)
            ->assertJsonPath('folio.status', Folio::STATUS_OPEN);

        $stay = Stay::on('tenant')->find($response->json('stay.id'));
        $this->assertNotNull($stay);
        $this->assertNotNull($stay->getAttribute('folio_id'));
        $this->assertSame(
            Reservation::STATUS_CHECKED_IN,
            $reservation->fresh()->getAttribute('status')
        );
    }

    public function test_three_night_half_board_posts_three_room_and_three_board_charges(): void
    {
        [$reservation, $folioId] = $this->checkedInThreeNightHalfBoard();

        foreach (['2027-01-10', '2027-01-11', '2027-01-12'] as $date) {
            $this->actingAsStaff($this->owner)
                ->postJson('/api/hotel/nightly-posting/run', ['date' => $date])
                ->assertOk()
                ->assertJsonPath('data.posted', 1);
        }

        $roomCharges = Charge::on('tenant')
            ->where('folio_id', $folioId)
            ->where('source_type', Charge::SOURCE_ROOM_NIGHT)
            ->get();
        $boardCharges = Charge::on('tenant')
            ->where('folio_id', $folioId)
            ->where('source_type', Charge::SOURCE_BOARD)
            ->get();

        $this->assertCount(3, $roomCharges);
        $this->assertCount(3, $boardCharges);
        $this->assertSame(3 * 15000, (int) $roomCharges->sum('amount_cents'));
        $this->assertSame(3 * 5000 * 2, (int) $boardCharges->sum('amount_cents'));
        $receptionId = (int) Outlet::on('tenant')->where('outlet_type', 'reception')->value('id');
        $this->assertSame($receptionId, (int) $roomCharges->first()->getAttribute('outlet_id'));
        $this->assertSame($receptionId, (int) $boardCharges->first()->getAttribute('outlet_id'));

        // Retry of the same night must not duplicate charges.
        $this->actingAsStaff($this->owner)
            ->postJson('/api/hotel/nightly-posting/run', ['date' => '2027-01-10'])
            ->assertOk();

        $this->assertSame(3, Charge::on('tenant')
            ->where('folio_id', $folioId)
            ->where('source_type', Charge::SOURCE_ROOM_NIGHT)
            ->count());
    }

    public function test_checkout_is_blocked_until_folio_balance_is_zero(): void
    {
        [$reservation, $folioId] = $this->checkedInThreeNightHalfBoard();
        $stayId = (int) Stay::on('tenant')->where('reservation_id', $reservation->getKey())->value('id');

        foreach (['2027-01-10', '2027-01-11', '2027-01-12'] as $date) {
            $this->actingAsStaff($this->owner)->postJson('/api/hotel/nightly-posting/run', ['date' => $date]);
        }

        $balance = (int) Charge::on('tenant')->where('folio_id', $folioId)->sum('amount_cents');
        $this->assertSame(75000, $balance);

        $this->actingAsStaff($this->frontDesk)
            ->postJson('/api/hotel/stays/'.$stayId.'/check-out')
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'folio_balance_not_zero');

        // Split tender: card + cash.
        $this->actingAsStaff($this->frontDesk)
            ->postJson('/api/folios/'.$folioId.'/payments', ['method' => 'card', 'amount_cents' => 40000])
            ->assertCreated();
        $this->actingAsStaff($this->frontDesk)
            ->postJson('/api/folios/'.$folioId.'/payments', ['method' => 'cash', 'amount_cents' => 35000])
            ->assertCreated();

        $this->actingAsStaff($this->frontDesk)
            ->postJson('/api/hotel/stays/'.$stayId.'/check-out')
            ->assertOk()
            ->assertJsonPath('status', Stay::STATUS_CHECKED_OUT);

        $this->assertSame(
            Folio::STATUS_CLOSED,
            Folio::on('tenant')->find($folioId)->getAttribute('status')
        );
    }

    public function test_folio_void_needs_manager_and_keeps_money_rows_append_only(): void
    {
        $folio = $this->openFolioForVoid();
        $charge = $this->chargeForVoid($folio, 'Summit lunch');

        $this->actingAsStaff($this->frontDesk)->postJson('/api/folios/'.$folio->getKey().'/voids', [
            'charge_id' => $charge->getKey(),
            'reason' => 'Wrong table',
        ])->assertStatus(403);

        $this->actingAsStaff($this->manager)->postJson('/api/folios/'.$folio->getKey().'/voids', [
            'charge_id' => $charge->getKey(),
            'reason' => 'Wrong table',
        ])->assertCreated()
            ->assertJsonPath('reason_code', FolioVoid::REASON_OTHER);

        $voided = Charge::on('tenant')->find($charge->getKey());
        $this->assertTrue((bool) $voided->getAttribute('is_voided'));
        $this->assertSame(2500, (int) $voided->getAttribute('amount_cents'));

        $this->expectException(DomainException::class);
        $voided->setAttribute('amount_cents', 9999);
        $voided->save();
    }

    public function test_folio_void_preserves_explicit_weather_reason_code(): void
    {
        $folio = $this->openFolioForVoid();
        $charge = $this->chargeForVoid($folio, 'Weather-affected activity');

        $this->actingAsStaff($this->manager)->postJson('/api/folios/'.$folio->getKey().'/voids', [
            'charge_id' => $charge->getKey(),
            'reason' => 'Cancelled due to unsafe weather',
            'reason_code' => FolioVoid::REASON_WEATHER,
        ])->assertCreated()
            ->assertJsonPath('reason_code', FolioVoid::REASON_WEATHER);
    }

    public function test_charge_requires_a_valid_active_outlet(): void
    {
        $folio = $this->openFolioForVoid();
        $url = '/api/folios/'.$folio->getKey().'/charges';
        $payload = [
            'description' => 'Manual charge',
            'amount_cents' => 2500,
        ];

        $this->actingAsStaff($this->owner)
            ->postJson($url, $payload)
            ->assertStatus(400)
            ->assertJsonPath('error.code', 'error')
            ->assertJsonStructure(['error' => ['details' => ['outlet_id']]]);

        $this->actingAsStaff($this->owner)
            ->postJson($url, $payload + ['outlet_id' => 99999])
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'outlet_required');

        $inactiveOutlet = $this->outlet($this->building(), 'Inactive Reception', 'reception');
        $inactiveOutlet->setAttribute('is_active', false);
        $inactiveOutlet->save();
        $this->actingAsStaff($this->owner)
            ->postJson($url, $payload + ['outlet_id' => $inactiveOutlet->getKey()])
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'outlet_required');

        $activeOutlet = $this->outlet($this->building(), 'Active Reception', 'reception');
        $this->actingAsStaff($this->owner)
            ->postJson($url, $payload + ['outlet_id' => $activeOutlet->getKey()])
            ->assertCreated()
            ->assertJsonPath('outlet_id', (int) $activeOutlet->getKey());

        $this->assertSame(1, Charge::on('tenant')->where('folio_id', $folio->getKey())->count());
    }

    public function test_charge_to_room_posts_to_the_selected_outlet(): void
    {
        $folio = $this->openFolioForVoid();
        $reservation = Reservation::on('tenant')->create([
            'reference' => 'CHARGE-TO-ROOM',
            'guest_name' => 'Room Charge Guest',
            'check_in_on' => '2027-01-10',
            'check_out_on' => '2027-01-11',
            'adults' => 1,
            'children' => 0,
            'status' => Reservation::STATUS_CHECKED_IN,
        ]);
        $stay = Stay::on('tenant')->create([
            'reservation_id' => $reservation->getKey(),
            'folio_id' => $folio->getKey(),
            'checked_in_at' => now(),
            'status' => Stay::STATUS_IN_HOUSE,
        ]);
        $outlet = $this->outlet($this->building(), 'Charge-to-room outlet', 'restaurant');

        $charge = app(ChargeToRoomService::class)->charge((int) $stay->getKey(), [
            'outlet_id' => (int) $outlet->getKey(),
            'description' => 'Restaurant order',
            'amount_cents' => 1800,
            'source_type' => Charge::SOURCE_ORDER,
        ]);

        $this->assertSame((int) $outlet->getKey(), (int) $charge->getAttribute('outlet_id'));
        $this->assertSame((int) $stay->getKey(), (int) $charge->getAttribute('stay_id'));
    }

    public function test_folio_and_outlet_deletes_cannot_remove_charge_or_payment_history(): void
    {
        $folio = $this->openFolioForVoid();
        $building = $this->building();
        $outlet = Outlet::on('tenant')
            ->where('building_id', $building->getKey())
            ->where('outlet_type', 'reception')
            ->firstOrFail();
        $charge = Charge::on('tenant')->create([
            'folio_id' => $folio->getKey(),
            'outlet_id' => $outlet->getKey(),
            'posted_on' => now()->toDateString(),
            'description' => 'Protected charge',
            'amount_cents' => 2500,
            'tax_cents' => 0,
            'source_type' => Charge::SOURCE_ORDER,
            'is_voided' => false,
        ]);
        $payment = app(\Modules\Folio\App\Services\FolioService::class)->recordPayment($folio, [
            'method' => Payment::METHOD_CASH,
            'amount_cents' => 2500,
        ]);

        try {
            $folio->delete();
            $this->fail('A folio with ledger entries must not be deleted.');
        } catch (QueryException) {
            $this->assertTrue($folio->exists);
        }

        try {
            $outlet->delete();
            $this->fail('An outlet with charge history must not be deleted.');
        } catch (QueryException) {
            $this->assertTrue($outlet->exists);
        }

        $this->assertModelExists($charge);
        $this->assertModelExists($payment);
    }

    public function test_failed_nightly_posting_can_retry_after_lodging_outlet_is_configured(): void
    {
        $season = $this->season();
        $type = $this->roomType();
        $this->price($type, $season, 15000);
        $room = $this->room($type);
        $board = $this->boardPlan(BoardPlan::HALF_BOARD, 5000);
        $folio = $this->openFolioForVoid();
        $reservation = Reservation::on('tenant')->create([
            'reference' => 'NIGHTLY-RETRY',
            'guest_name' => 'Retry Guest',
            'check_in_on' => '2027-01-10',
            'check_out_on' => '2027-01-13',
            'adults' => 2,
            'children' => 0,
            'board_plan_id' => $board->getKey(),
            'status' => Reservation::STATUS_CHECKED_IN,
        ]);
        ReservationAccommodation::on('tenant')->create([
            'reservation_id' => $reservation->getKey(),
            'accommodation_type' => ReservationAccommodation::TYPE_ROOM,
            'room_id' => $room->getKey(),
        ]);
        $stay = Stay::on('tenant')->create([
            'reservation_id' => $reservation->getKey(),
            'folio_id' => $folio->getKey(),
            'room_id' => $room->getKey(),
            'checked_in_at' => now(),
            'status' => Stay::STATUS_IN_HOUSE,
        ]);
        $outlet = Outlet::on('tenant')
            ->where('building_id', $room->getAttribute('building_id'))
            ->where('outlet_type', 'reception')
            ->firstOrFail();
        $outlet->delete();

        $postingService = app(NightlyPostingService::class);
        $failed = $postingService->postNight($stay, '2027-01-10');
        $this->assertSame(NightlyPosting::STATUS_FAILED, $failed->getAttribute('status'));
        $this->assertSame(0, Charge::on('tenant')->where('folio_id', $folio->getKey())->count());

        $reception = $this->outlet($room->building, 'Reception 2', 'reception');
        $posted = $postingService->postNight($stay, '2027-01-10');
        $this->assertSame(NightlyPosting::STATUS_POSTED, $posted->getAttribute('status'));

        $charges = Charge::on('tenant')->where('folio_id', $folio->getKey())->get();
        $this->assertCount(2, $charges);
        $this->assertSame(
            [(int) $reception->getKey(), (int) $reception->getKey()],
            $charges->pluck('outlet_id')->map(fn ($id) => (int) $id)->all()
        );
        $this->assertSame(1, $charges->where('source_type', Charge::SOURCE_BOARD)->count());
        $this->assertSame(
            NightlyPosting::STATUS_POSTED,
            NightlyPosting::on('tenant')->where('stay_id', $stay->getKey())
                ->whereDate('posted_on', '2027-01-10')->value('status')
        );
    }

    public function test_hut_bed_nightly_charge_uses_the_building_hut_kitchen(): void
    {
        $building = Building::on('tenant')->create([
            'name' => 'Mountain Hut',
            'building_type' => 'mountain_hut',
            'altitude_m' => 2100,
            'is_active' => true,
        ]);
        $kitchen = $this->outlet($building, 'Kitchen', 'hut_kitchen');
        $season = $this->season();
        $type = $this->roomType(['name' => 'Dorm Bed']);
        $this->price($type, $season, 4000);
        $dorm = HutDormitory::on('tenant')->create([
            'building_id' => $building->getKey(),
            'room_type_id' => $type->getKey(),
            'name' => 'Dormitory',
            'is_active' => true,
        ]);
        $bed = $this->bed($dorm, '01');
        $folio = $this->openFolioForVoid();
        $reservation = Reservation::on('tenant')->create([
            'reference' => 'HUT-NIGHTLY',
            'guest_name' => 'Hut Guest',
            'check_in_on' => '2027-01-10',
            'check_out_on' => '2027-01-11',
            'adults' => 1,
            'children' => 0,
            'status' => Reservation::STATUS_CHECKED_IN,
        ]);
        ReservationAccommodation::on('tenant')->create([
            'reservation_id' => $reservation->getKey(),
            'accommodation_type' => ReservationAccommodation::TYPE_BED,
            'hut_bed_id' => $bed->getKey(),
        ]);
        $stay = Stay::on('tenant')->create([
            'reservation_id' => $reservation->getKey(),
            'folio_id' => $folio->getKey(),
            'hut_bed_id' => $bed->getKey(),
            'checked_in_at' => now(),
            'status' => Stay::STATUS_IN_HOUSE,
        ]);

        $posting = app(NightlyPostingService::class)->postNight($stay, '2027-01-10');
        $this->assertSame(NightlyPosting::STATUS_POSTED, $posting->getAttribute('status'));
        $charge = Charge::on('tenant')->where('folio_id', $folio->getKey())->firstOrFail();
        $this->assertSame((int) $kitchen->getKey(), (int) $charge->getAttribute('outlet_id'));
    }

    public function test_board_plan_exposes_meal_inclusions_for_pos(): void
    {
        $halfBoard = $this->boardPlan(BoardPlan::HALF_BOARD, 5000);

        $this->assertTrue($halfBoard->includesMeal('dinner'));
        $this->assertFalse($halfBoard->includesMeal('lunch'));

        $response = $this->actingAsStaff($this->frontDesk)
            ->getJson('/api/hotel/board-plans/'.$halfBoard->getKey());

        $response->assertOk()
            ->assertJsonPath('code', BoardPlan::HALF_BOARD)
            ->assertJsonPath('inclusions', ['breakfast', 'dinner']);
    }

    private function building(): Building
    {
        $building = Building::on('tenant')->create([
            'name' => 'Main Hotel',
            'building_type' => 'main_hotel',
            'altitude_m' => 1200,
            'is_active' => true,
        ]);

        $this->outlet($building, 'Reception', 'reception');
        $this->outlet($building, 'Kitchen', 'hut_kitchen');

        return $building;
    }

    private function outlet(Building $building, string $name, string $type): Outlet
    {
        return Outlet::on('tenant')->create([
            'building_id' => $building->getKey(),
            'name' => $name,
            'outlet_type' => $type,
            'is_active' => true,
        ]);
    }

    private function season(): Season
    {
        return Season::on('tenant')->create([
            'kind' => 'winter',
            'starts_on' => '2026-12-01',
            'ends_on' => '2027-03-31',
            'is_active' => true,
        ]);
    }

    private function roomType(array $attributes = []): RoomType
    {
        return RoomType::on('tenant')->create(array_merge([
            'name' => 'Standard',
            'max_adults' => 2,
            'max_children' => 1,
            'is_active' => true,
        ], $attributes));
    }

    private function price(RoomType $roomType, Season $season, int $cents): void
    {
        \Modules\Hotel\App\Models\RoomSeasonPrice::on('tenant')->create([
            'room_type_id' => $roomType->getKey(),
            'season_id' => $season->getKey(),
            'amount_cents' => $cents,
        ]);
    }

    private function boardPlan(string $code, int $cents): BoardPlan
    {
        return BoardPlan::on('tenant')->create([
            'code' => $code,
            'name' => BoardPlan::CODES[$code],
            'price_per_person_cents' => $cents,
            'is_active' => true,
        ]);
    }

    private function room(RoomType $type): Room
    {
        return Room::on('tenant')->create([
            'building_id' => $this->building()->getKey(),
            'room_type_id' => $type->getKey(),
            'room_number' => '101',
            'accommodation_type' => Room::TYPE_ROOM,
            'is_active' => true,
        ]);
    }

    private function bed(HutDormitory $dorm, string $number): HutBed
    {
        return HutBed::on('tenant')->create([
            'hut_dormitory_id' => $dorm->getKey(),
            'bed_number' => $number,
            'is_active' => true,
        ]);
    }

    private function reserveRoom(Room $room, string $checkIn, string $checkOut, array $overrides = [])
    {
        return $this->actingAsStaff($this->frontDesk)->postJson('/api/hotel/reservations', array_merge([
            'guest_name' => 'Summit Guest',
            'check_in_on' => $checkIn,
            'check_out_on' => $checkOut,
            'adults' => 2,
            'allocations' => [
                ['accommodation_type' => 'room', 'room_id' => $room->getKey()],
            ],
        ], $overrides));
    }

    private function reserveBed(HutBed $bed, string $checkIn, string $checkOut)
    {
        return $this->actingAsStaff($this->frontDesk)->postJson('/api/hotel/reservations', [
            'guest_name' => 'Hut Guest',
            'check_in_on' => $checkIn,
            'check_out_on' => $checkOut,
            'adults' => 1,
            'allocations' => [
                ['accommodation_type' => 'bed', 'hut_bed_id' => $bed->getKey()],
            ],
        ]);
    }

    /**
     * @return array{0: Reservation, 1: int}
     */
    private function threeNightHalfBoardReservation(): array
    {
        $season = $this->season();
        $type = $this->roomType();
        $this->price($type, $season, 15000);
        $room = $this->room($type);
        $board = $this->boardPlan(BoardPlan::HALF_BOARD, 5000);

        $response = $this->reserveRoom($room, '2027-01-10', '2027-01-13', [
            'board_plan_id' => $board->getKey(),
        ])->assertCreated();

        $reservation = Reservation::on('tenant')->find($response->json('id'));

        return [$reservation, 0];
    }

    /**
     * @return array{0: Reservation, 1: int}
     */
    private function checkedInThreeNightHalfBoard(): array
    {
        [$reservation] = $this->threeNightHalfBoardReservation();

        $response = $this->actingAsStaff($this->frontDesk)
            ->postJson('/api/hotel/reservations/'.$reservation->getKey().'/check-in')
            ->assertCreated();

        return [$reservation, (int) $response->json('folio.id')];
    }

    private function openFolioForVoid(): Folio
    {
        return Folio::on('tenant')->create([
            'reference' => 'VOID-TEST',
            'status' => Folio::STATUS_OPEN,
            'currency' => 'EUR',
            'opened_at' => now(),
        ]);
    }

    private function chargeForVoid(Folio $folio, string $description): Charge
    {
        $building = $this->building();
        $outlet = Outlet::on('tenant')->where('building_id', $building->getKey())
            ->where('outlet_type', 'reception')->firstOrFail();

        return Charge::on('tenant')->create([
            'folio_id' => $folio->getKey(),
            'outlet_id' => $outlet->getKey(),
            'posted_on' => now()->toDateString(),
            'description' => $description,
            'amount_cents' => 2500,
            'tax_cents' => 0,
            'source_type' => Charge::SOURCE_ORDER,
            'is_voided' => false,
        ]);
    }
}
