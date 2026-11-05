<?php

namespace Modules\Hotel\App\Services;

use App\Support\Http\Envelope;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\AuditEvent;
use Modules\Core\App\Support\CoreAudit;
use Modules\Core\App\Models\TenantSettings;
use Modules\Folio\App\Services\FolioService;
use Modules\Hotel\App\Models\Reservation;
use Modules\Hotel\App\Models\ReservationAccommodation;
use Modules\Hotel\App\Models\Stay;

/** Check-in opens the Stay and its guest Folio (PDF Module B rule). */
class CheckInService
{
    public function __construct(private readonly FolioService $folios)
    {
    }

    public function checkIn(Reservation $reservation): Stay
    {
        if (! in_array($reservation->getAttribute('status'), Reservation::CHECK_IN_STATUSES, true)) {
            Envelope::abort(
                'reservation_not_checkinable',
                'Only pending or confirmed reservations can be checked in.',
                ['status' => $reservation->getAttribute('status')],
                422
            );
        }

        $allocations = ReservationAccommodation::on(config('tenancy.connection_name'))
            ->where('reservation_id', $reservation->getKey())
            ->get();

        if ($allocations->isEmpty()) {
            Envelope::abort('reservation_empty', 'Reservation has no accommodation allocation.', [], 422);
        }

        return DB::connection(config('tenancy.connection_name'))->transaction(function () use ($reservation, $allocations) {
            $currency = TenantSettings::on(config('tenancy.connection_name'))->value('currency') ?? 'EUR';
            $folio = $this->folios->open($reservation->getAttribute('reference'), (string) $currency);

            /** @var ReservationAccommodation $primary */
            $primary = $allocations->first();

            $stay = Stay::on(config('tenancy.connection_name'))->create([
                'reservation_id' => $reservation->getKey(),
                'folio_id' => $folio->getKey(),
                'room_id' => $primary->getAttribute('accommodation_type') === ReservationAccommodation::TYPE_ROOM
                    ? $primary->getAttribute('room_id')
                    : null,
                'hut_bed_id' => $primary->getAttribute('accommodation_type') === ReservationAccommodation::TYPE_BED
                    ? $primary->getAttribute('hut_bed_id')
                    : null,
                'checked_in_at' => now(),
                'status' => Stay::STATUS_IN_HOUSE,
            ]);

            $old = $reservation->toApiArray();
            $reservation->setAttribute('status', Reservation::STATUS_CHECKED_IN);
            $reservation->save();

            CoreAudit::record(
                AuditEvent::ACTION_CREATE,
                'hotel_stay',
                (int) $stay->getKey(),
                newValues: array_merge($stay->fresh()->toApiArray(), ['folio_id' => (int) $folio->getKey()]),
            );
            CoreAudit::record(
                AuditEvent::ACTION_UPDATE,
                'hotel_reservation',
                (int) $reservation->getKey(),
                oldValues: $old,
                newValues: $reservation->fresh()->toApiArray(),
                reason: 'check-in',
            );

            return $stay->fresh();
        });
    }
}
