<?php

namespace Modules\Hotel\App\Services;

use App\Support\Http\Envelope;
use Modules\Hotel\App\Models\HutBed;
use Modules\Hotel\App\Models\HutDormitory;
use Modules\Hotel\App\Models\Reservation;
use Modules\Hotel\App\Models\ReservationAccommodation;
use Modules\Hotel\App\Models\Room;

/**
 * Date-overlap protection for rooms, chalets and individual hut beds.
 * Night based: existing_check_in < requested_check_out AND existing_check_out > requested_check_in.
 */
class AvailabilityService
{
    /**
     * @return list<int>
     */
    public function takenRoomIds(string $checkInOn, string $checkOutOn, ?int $ignoreReservationId = null): array
    {
        return $this->overlapping($checkInOn, $checkOutOn, $ignoreReservationId)
            ->whereNotNull('hotel_reservation_accommodation.room_id')
            ->pluck('hotel_reservation_accommodation.room_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @return list<int>
     */
    public function takenBedIds(string $checkInOn, string $checkOutOn, ?int $ignoreReservationId = null): array
    {
        return $this->overlapping($checkInOn, $checkOutOn, $ignoreReservationId)
            ->whereNotNull('hotel_reservation_accommodation.hut_bed_id')
            ->pluck('hotel_reservation_accommodation.hut_bed_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function assertRoomAvailable(int $roomId, string $checkInOn, string $checkOutOn, ?int $ignoreReservationId = null): void
    {
        if (in_array($roomId, $this->takenRoomIds($checkInOn, $checkOutOn, $ignoreReservationId), true)) {
            Envelope::abort(
                'accommodation_unavailable',
                'The room or chalet is already reserved for an overlapping date range.',
                ['room_id' => $roomId, 'check_in_on' => $checkInOn, 'check_out_on' => $checkOutOn],
                409
            );
        }
    }

    public function assertBedAvailable(int $bedId, string $checkInOn, string $checkOutOn, ?int $ignoreReservationId = null): void
    {
        if (in_array($bedId, $this->takenBedIds($checkInOn, $checkOutOn, $ignoreReservationId), true)) {
            Envelope::abort(
                'accommodation_unavailable',
                'The hut bed is already reserved for an overlapping date range.',
                ['hut_bed_id' => $bedId, 'check_in_on' => $checkInOn, 'check_out_on' => $checkOutOn],
                409
            );
        }
    }

    /**
     * Availability read model for the calendar / booking form.
     *
     * @return array{rooms: list<array<string, mixed>>, beds: list<array<string, mixed>>}
     */
    public function availability(string $checkInOn, string $checkOutOn, ?int $buildingId = null): array
    {
        $takenRooms = $this->takenRoomIds($checkInOn, $checkOutOn);
        $takenBeds = $this->takenBedIds($checkInOn, $checkOutOn);

        $rooms = Room::on(config('tenancy.connection_name'))
            ->where('is_active', true)
            ->when($buildingId !== null, fn ($q) => $q->where('building_id', $buildingId))
            ->orderBy('building_id')
            ->orderBy('room_number')
            ->get()
            ->map(fn (Room $room) => [
                'id' => (int) $room->getKey(),
                'building_id' => (int) $room->getAttribute('building_id'),
                'room_type_id' => (int) $room->getAttribute('room_type_id'),
                'room_number' => $room->getAttribute('room_number'),
                'name' => $room->getAttribute('name'),
                'accommodation_type' => $room->getAttribute('accommodation_type'),
                'available' => ! in_array((int) $room->getKey(), $takenRooms, true),
            ])
            ->values()
            ->all();

        $dormitoryIds = HutDormitory::on(config('tenancy.connection_name'))
            ->when($buildingId !== null, fn ($q) => $q->where('building_id', $buildingId))
            ->pluck('id')
            ->all();

        $beds = HutBed::on(config('tenancy.connection_name'))
            ->where('is_active', true)
            ->whereIn('hut_dormitory_id', $dormitoryIds === [] ? [0] : $dormitoryIds)
            ->orderBy('hut_dormitory_id')
            ->orderBy('bed_number')
            ->get()
            ->map(fn (HutBed $bed) => [
                'id' => (int) $bed->getKey(),
                'hut_dormitory_id' => (int) $bed->getAttribute('hut_dormitory_id'),
                'bed_number' => $bed->getAttribute('bed_number'),
                'available' => ! in_array((int) $bed->getKey(), $takenBeds, true),
            ])
            ->values()
            ->all();

        return ['rooms' => $rooms, 'beds' => $beds];
    }

    /**
     * Allocations overlapping a window, for the building/room/date calendar grid.
     *
     * @return list<array<string, mixed>>
     */
    public function calendar(?int $buildingId, string $from, string $to): array
    {
        $roomIds = Room::on(config('tenancy.connection_name'))
            ->when($buildingId !== null, fn ($q) => $q->where('building_id', $buildingId))
            ->pluck('id')
            ->all();

        $dormitoryIds = HutDormitory::on(config('tenancy.connection_name'))
            ->when($buildingId !== null, fn ($q) => $q->where('building_id', $buildingId))
            ->pluck('id')
            ->all();

        $bedIds = HutBed::on(config('tenancy.connection_name'))
            ->whereIn('hut_dormitory_id', $dormitoryIds === [] ? [0] : $dormitoryIds)
            ->pluck('id')
            ->all();

        $query = $this->overlapping($from, $to)
            ->select([
                'hotel_reservation_accommodation.id',
                'hotel_reservation_accommodation.accommodation_type',
                'hotel_reservation_accommodation.room_id',
                'hotel_reservation_accommodation.hut_bed_id',
                'hotel_reservation.id as reservation_id',
                'hotel_reservation.reference',
                'hotel_reservation.guest_name',
                'hotel_reservation.check_in_on',
                'hotel_reservation.check_out_on',
                'hotel_reservation.status',
            ]);

        if ($roomIds !== [] && $bedIds !== []) {
            $query->where(function ($q) use ($roomIds, $bedIds) {
                $q->whereIn('hotel_reservation_accommodation.room_id', $roomIds)
                    ->orWhereIn('hotel_reservation_accommodation.hut_bed_id', $bedIds);
            });
        }
        elseif ($roomIds !== []) {
            $query->whereIn('hotel_reservation_accommodation.room_id', $roomIds);
        }
        else {
            $query->whereIn('hotel_reservation_accommodation.hut_bed_id', $bedIds === [] ? [0] : $bedIds);
        }

        return $query->orderBy('hotel_reservation.check_in_on')
            ->get()
            ->map(fn ($row) => [
                'id' => (int) $row->id,
                'reservation_id' => (int) $row->reservation_id,
                'reference' => $row->reference,
                'guest_name' => $row->guest_name,
                'accommodation_type' => $row->accommodation_type,
                'room_id' => $row->room_id === null ? null : (int) $row->room_id,
                'hut_bed_id' => $row->hut_bed_id === null ? null : (int) $row->hut_bed_id,
                'check_in_on' => (string) $row->check_in_on,
                'check_out_on' => (string) $row->check_out_on,
                'status' => $row->status,
            ])
            ->values()
            ->all();
    }

    private function overlapping(string $checkInOn, string $checkOutOn, ?int $ignoreReservationId = null)
    {
        return ReservationAccommodation::on(config('tenancy.connection_name'))
            ->join('hotel_reservation', 'hotel_reservation.id', '=', 'hotel_reservation_accommodation.reservation_id')
            ->whereIn('hotel_reservation.status', Reservation::BLOCKING_STATUSES)
            ->where('hotel_reservation.check_in_on', '<', $checkOutOn)
            ->where('hotel_reservation.check_out_on', '>', $checkInOn)
            ->when($ignoreReservationId !== null, fn ($q) => $q->where('hotel_reservation.id', '!=', $ignoreReservationId));
    }
}
