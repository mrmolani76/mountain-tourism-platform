<?php

namespace Modules\Hotel\App\Services;

use App\Support\Http\Envelope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Core\App\Models\AuditEvent;
use Modules\Core\App\Support\CoreAudit;
use Modules\Hotel\App\Models\BoardPlan;
use Modules\Hotel\App\Models\HutBed;
use Modules\Hotel\App\Models\Reservation;
use Modules\Hotel\App\Models\ReservationAccommodation;
use Modules\Hotel\App\Models\Room;
use Modules\Hotel\App\Models\RoomType;

/**
 * Reservation create/update/cancel with availability guard + price snapshots.
 * One reservation may allocate several rooms/beds and one board plan (MVP default).
 */
class ReservationService
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly PricingService $pricing,
    ) {
    }

    public function create(array $data): Reservation
    {
        return DB::connection(config('tenancy.connection_name'))->transaction(function () use ($data) {
            $allocations = $data['allocations'];
            $boardPlan = $this->boardPlan($data['board_plan_id'] ?? null);
            $prepared = $this->prepareAllocations(
                $allocations,
                $data['check_in_on'],
                $data['check_out_on'],
                $boardPlan,
                null
            );

            $reservation = Reservation::on(config('tenancy.connection_name'))->create([
                'reference' => $this->nextReference(),
                'guest_name' => $data['guest_name'],
                'guest_email' => $data['guest_email'] ?? null,
                'guest_phone' => $data['guest_phone'] ?? null,
                'check_in_on' => $data['check_in_on'],
                'check_out_on' => $data['check_out_on'],
                'adults' => (int) ($data['adults'] ?? 1),
                'children' => (int) ($data['children'] ?? 0),
                'board_plan_id' => $boardPlan?->getKey(),
                'deposit_cents' => (int) ($data['deposit_cents'] ?? 0),
                'arrival_mode' => $data['arrival_mode'] ?? null,
                'arrival_time' => $data['arrival_time'] ?? null,
                'arrival_station' => $data['arrival_station'] ?? null,
                'status' => $data['status'] ?? Reservation::STATUS_PENDING,
                'notes' => $data['notes'] ?? null,
                'created_by' => $this->actorId(),
            ]);

            $this->storeAllocations($reservation, $prepared, $boardPlan);
            $fresh = $reservation->fresh();
            CoreAudit::record(
                AuditEvent::ACTION_CREATE,
                'hotel_reservation',
                (int) $fresh->getKey(),
                newValues: $fresh->toApiArray(),
            );

            return $fresh;
        });
    }

    public function update(Reservation $reservation, array $data): Reservation
    {
        if (in_array($reservation->getAttribute('status'), [Reservation::STATUS_CHECKED_IN, Reservation::STATUS_CHECKED_OUT], true)) {
            Envelope::abort('reservation_locked', 'Checked-in or checked-out reservations cannot be edited.', [], 422);
        }

        return DB::connection(config('tenancy.connection_name'))->transaction(function () use ($reservation, $data) {
            $old = $reservation->toApiArray();
            $checkIn = $data['check_in_on'] ?? $reservation->getAttribute('check_in_on')->toDateString();
            $checkOut = $data['check_out_on'] ?? $reservation->getAttribute('check_out_on')->toDateString();
            $boardPlan = array_key_exists('board_plan_id', $data)
                ? $this->boardPlan($data['board_plan_id'])
                : $reservation->boardPlan;

            $payload = $data;
            unset($payload['allocations']);
            $reservation->fill($payload);
            $reservation->save();

            $allocations = $data['allocations'] ?? null;
            if (is_array($allocations)) {
                $prepared = $this->prepareAllocations(
                    $allocations,
                    $checkIn,
                    $checkOut,
                    $boardPlan,
                    (int) $reservation->getKey()
                );
                ReservationAccommodation::on(config('tenancy.connection_name'))
                    ->where('reservation_id', $reservation->getKey())
                    ->delete();
                $this->storeAllocations($reservation, $prepared, $boardPlan);
            }

            $fresh = $reservation->fresh();
            CoreAudit::record(
                AuditEvent::ACTION_UPDATE,
                'hotel_reservation',
                (int) $fresh->getKey(),
                oldValues: $old,
                newValues: $fresh->toApiArray(),
            );

            return $fresh;
        });
    }

    public function cancel(Reservation $reservation, ?string $reason = null): Reservation
    {
        if (in_array($reservation->getAttribute('status'), [Reservation::STATUS_CHECKED_IN, Reservation::STATUS_CHECKED_OUT], true)) {
            Envelope::abort('reservation_locked', 'Checked-in or checked-out reservations cannot be cancelled.', [], 422);
        }

        $old = $reservation->toApiArray();
        $reservation->setAttribute('status', Reservation::STATUS_CANCELLED);
        $reservation->save();
        CoreAudit::record(
            AuditEvent::ACTION_UPDATE,
            'hotel_reservation',
            (int) $reservation->getKey(),
            oldValues: $old,
            newValues: $reservation->fresh()->toApiArray(),
            reason: $reason ?? 'cancelled',
        );

        return $reservation->fresh();
    }

    private function boardPlan(mixed $boardPlanId): ?BoardPlan
    {
        if ($boardPlanId === null || $boardPlanId === '') {
            return null;
        }

        $plan = BoardPlan::on(config('tenancy.connection_name'))->find((int) $boardPlanId);
        if ($plan === null) {
            Envelope::abort('not_found', 'Board plan not found.', [], 404);
        }

        return $plan;
    }

    /**
     * @param  list<array<string, mixed>>  $allocations
     * @return list<array<string, mixed>>
     */
    private function prepareAllocations(
        array $allocations,
        string $checkInOn,
        string $checkOutOn,
        ?BoardPlan $boardPlan,
        ?int $ignoreReservationId,
    ): array {
        $prepared = [];

        foreach ($allocations as $row) {
            $type = $row['accommodation_type'];

            if ($type === ReservationAccommodation::TYPE_BED) {
                $bed = HutBed::on(config('tenancy.connection_name'))->with('dormitory.roomType')->find((int) $row['hut_bed_id']);
                if ($bed === null || ! $bed->getAttribute('is_active')) {
                    Envelope::abort('not_found', 'Hut bed not found or inactive.', [], 404);
                }
                $this->availability->assertBedAvailable((int) $bed->getKey(), $checkInOn, $checkOutOn, $ignoreReservationId);
                $roomType = $bed->dormitory?->roomType;
                $roomId = null;
                $bedId = (int) $bed->getKey();
            }
            else {
                $room = Room::on(config('tenancy.connection_name'))->with('roomType')->find((int) $row['room_id']);
                if ($room === null || ! $room->getAttribute('is_active')) {
                    Envelope::abort('not_found', 'Room not found or inactive.', [], 404);
                }
                $this->availability->assertRoomAvailable((int) $room->getKey(), $checkInOn, $checkOutOn, $ignoreReservationId);
                $roomType = $room->roomType;
                $roomId = (int) $room->getKey();
                $bedId = null;
            }

            if (! $roomType instanceof RoomType) {
                Envelope::abort(
                    'pricing_missing',
                    'Allocate a room type before pricing this accommodation.',
                    ['accommodation_type' => $type],
                    422
                );
            }

            $prepared[] = [
                'accommodation_type' => $type,
                'room_id' => $roomId,
                'hut_bed_id' => $bedId,
                'nightly_price_cents' => $this->pricing->roomNightPriceForDate($roomType, $checkInOn),
            ];
        }

        return $prepared;
    }

    /**
     * @param  list<array<string, mixed>>  $prepared
     */
    private function storeAllocations(Reservation $reservation, array $prepared, ?BoardPlan $boardPlan): void
    {
        foreach ($prepared as $row) {
            ReservationAccommodation::on(config('tenancy.connection_name'))->create([
                'reservation_id' => $reservation->getKey(),
                'accommodation_type' => $row['accommodation_type'],
                'room_id' => $row['room_id'],
                'hut_bed_id' => $row['hut_bed_id'],
                'nightly_price_cents' => $row['nightly_price_cents'],
                'board_price_per_person_cents' => $boardPlan === null
                    ? 0
                    : (int) $boardPlan->getAttribute('price_per_person_cents'),
            ]);
        }
    }

    private function nextReference(): string
    {
        do {
            $reference = 'RES-'.now()->format('Ymd').'-'.Str::upper(Str::random(4));
        } while (Reservation::on(config('tenancy.connection_name'))->where('reference', $reference)->exists());

        return $reference;
    }

    private function actorId(): ?int
    {
        if (app()->bound('staff')) {
            return (int) app('staff')->getKey();
        }

        return null;
    }
}
