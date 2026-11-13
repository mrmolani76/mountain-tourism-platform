<?php

namespace Modules\Folio\App\Services;

use App\Support\Http\Envelope;
use Modules\Folio\App\Models\Charge;
use Modules\Folio\App\Models\Folio;
use Modules\Hotel\App\Models\Reservation;
use Modules\Hotel\App\Models\Room;
use Modules\Hotel\App\Models\Stay;

/**
 * Resolve an in-house stay by room number or guest name, then post a charge
 * onto that stay's open folio (PDF Module C charge-to-room).
 */
class ChargeToRoomService
{
    public function __construct(private readonly FolioService $folios)
    {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function search(?string $roomNumber, ?string $guest): array
    {
        $roomNumber = $roomNumber !== null ? trim($roomNumber) : null;
        $guest = $guest !== null ? trim($guest) : null;

        if (($roomNumber === null || $roomNumber === '') && ($guest === null || $guest === '')) {
            Envelope::abort(
                'search_required',
                'Provide room_number and/or guest to search in-house stays.',
                [],
                400
            );
        }

        $query = Stay::on(config('tenancy.connection_name'))
            ->query()
            ->where('hotel_stay.status', Stay::STATUS_IN_HOUSE)
            ->whereNotNull('hotel_stay.folio_id')
            ->join('hotel_reservation', 'hotel_reservation.id', '=', 'hotel_stay.reservation_id')
            ->leftJoin('hotel_room', 'hotel_room.id', '=', 'hotel_stay.room_id')
            ->select([
                'hotel_stay.id as stay_id',
                'hotel_stay.folio_id',
                'hotel_stay.room_id',
                'hotel_stay.hut_bed_id',
                'hotel_stay.status as stay_status',
                'hotel_reservation.guest_name',
                'hotel_reservation.reference as reservation_reference',
                'hotel_room.room_number',
            ]);

        if ($roomNumber !== null && $roomNumber !== '') {
            $query->where('hotel_room.room_number', $roomNumber);
        }

        if ($guest !== null && $guest !== '') {
            $query->where('hotel_reservation.guest_name', 'like', '%'.$guest.'%');
        }

        return $query
            ->orderBy('hotel_stay.id')
            ->get()
            ->map(function ($row) {
                return [
                    'stay_id' => (int) $row->stay_id,
                    'folio_id' => (int) $row->folio_id,
                    'room_id' => $row->room_id === null ? null : (int) $row->room_id,
                    'hut_bed_id' => $row->hut_bed_id === null ? null : (int) $row->hut_bed_id,
                    'room_number' => $row->room_number,
                    'guest_name' => $row->guest_name,
                    'reservation_reference' => $row->reservation_reference,
                    'stay_status' => $row->stay_status,
                ];
            })
            ->all();
    }

    public function charge(int $stayId, array $data): Charge
    {
        $stay = Stay::on(config('tenancy.connection_name'))->find($stayId);
        if ($stay === null) {
            Envelope::abort('not_found', 'Stay not found.', [], 404);
        }

        if (! $stay->isInHouse()) {
            Envelope::abort(
                'stay_not_in_house',
                'Only an in-house stay can receive a charge to room.',
                ['stay_id' => $stayId, 'status' => $stay->getAttribute('status')],
                422
            );
        }

        $folioId = $stay->getAttribute('folio_id');
        $folio = $folioId === null
            ? null
            : Folio::on(config('tenancy.connection_name'))->find((int) $folioId);

        if ($folio === null) {
            Envelope::abort('folio_missing', 'Stay has no open folio.', ['stay_id' => $stayId], 422);
        }

        if (! $folio->isOpen()) {
            Envelope::abort('folio_closed', 'Folio is closed.', ['folio_id' => (int) $folio->getKey()], 422);
        }

        $payload = array_merge($data, [
            'stay_id' => (int) $stay->getKey(),
            'source_type' => $data['source_type'] ?? Charge::SOURCE_ORDER,
        ]);

        return $this->folios->postCharge($folio, $payload);
    }

    /**
     * Guest / room labels for invoice and list enrichment.
     *
     * @return array{guest_name: ?string, room_number: ?string, reservation_reference: ?string}
     */
    public function stayGuestContext(Stay $stay): array
    {
        $reservation = Reservation::on(config('tenancy.connection_name'))
            ->find($stay->getAttribute('reservation_id'));
        $room = $stay->getAttribute('room_id') === null
            ? null
            : Room::on(config('tenancy.connection_name'))->find((int) $stay->getAttribute('room_id'));

        return [
            'guest_name' => $reservation?->getAttribute('guest_name'),
            'room_number' => $room?->getAttribute('room_number'),
            'reservation_reference' => $reservation?->getAttribute('reference'),
        ];
    }
}
