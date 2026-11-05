<?php

namespace Tests\Feature\Modules\Hotel;

use DomainException;
use Modules\Core\App\Models\Building;
use Modules\Core\App\Models\Season;
use Modules\Folio\App\Models\Charge;
use Modules\Folio\App\Models\Folio;
use Modules\Hotel\App\Models\BoardPlan;
use Modules\Hotel\App\Models\HutBed;
use Modules\Hotel\App\Models\HutDormitory;
use Modules\Hotel\App\Models\Reservation;
use Modules\Hotel\App\Models\Room;
use Modules\Hotel\App\Models\RoomType;
use Modules\Hotel\App\Models\Stay;
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
            'Modules/Folio/Database/migrations/tenant/2026_11_04_000001_create_folio_tables.php',
            'Modules/Hotel/Database/migrations/tenant/2026_11_04_000001_create_hotel_tables.php',
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
        [, $folioId] = $this->checkedInThreeNightHalfBoard();

        $charge = $this->actingAsStaff($this->frontDesk)->postJson('/api/folios/'.$folioId.'/charges', [
            'description' => 'Summit lunch',
            'amount_cents' => 2500,
            'source_type' => Charge::SOURCE_ORDER,
        ]);
        $charge->assertCreated();
        $chargeId = (int) $charge->json('id');

        $this->actingAsStaff($this->frontDesk)->postJson('/api/folios/'.$folioId.'/voids', [
            'charge_id' => $chargeId,
            'reason' => 'Wrong table',
        ])->assertStatus(403);

        $this->actingAsStaff($this->manager)->postJson('/api/folios/'.$folioId.'/voids', [
            'charge_id' => $chargeId,
            'reason' => 'Wrong table',
        ])->assertCreated();

        $voided = Charge::on('tenant')->find($chargeId);
        $this->assertTrue((bool) $voided->getAttribute('is_voided'));
        $this->assertSame(2500, (int) $voided->getAttribute('amount_cents'));

        $this->expectException(DomainException::class);
        $voided->setAttribute('amount_cents', 9999);
        $voided->save();
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
        return Building::on('tenant')->create([
            'name' => 'Main Hotel',
            'building_type' => 'main_hotel',
            'altitude_m' => 1200,
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
}
