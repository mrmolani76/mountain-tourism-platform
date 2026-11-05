<?php

namespace Modules\Hotel\App\Services;

use App\Support\Http\Envelope;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\AuditEvent;
use Modules\Core\App\Support\CoreAudit;
use Modules\Folio\App\Models\Folio;
use Modules\Folio\App\Services\FolioService;
use Modules\Hotel\App\Models\Reservation;
use Modules\Hotel\App\Models\Stay;

/** Checkout gate: the folio must be at zero balance before the stay can close. */
class CheckoutService
{
    public function __construct(private readonly FolioService $folios)
    {
    }

    public function checkOut(Stay $stay): Stay
    {
        if (! $stay->isInHouse()) {
            Envelope::abort('stay_not_in_house', 'Only an in-house stay can be checked out.', [], 422);
        }

        $folioId = $stay->getAttribute('folio_id');
        $folio = $folioId === null
            ? null
            : Folio::on(config('tenancy.connection_name'))->find((int) $folioId);

        if ($folio === null) {
            Envelope::abort('folio_missing', 'Stay has no folio to settle.', [], 422);
        }

        return DB::connection(config('tenancy.connection_name'))->transaction(function () use ($stay, $folio) {
            $this->folios->close($folio);

            $old = $stay->toApiArray();
            $stay->setAttribute('status', Stay::STATUS_CHECKED_OUT);
            $stay->setAttribute('checked_out_at', now());
            $stay->save();

            $reservation = $stay->reservation;
            if ($reservation !== null) {
                $reservationOld = $reservation->toApiArray();
                $reservation->setAttribute('status', Reservation::STATUS_CHECKED_OUT);
                $reservation->save();
                CoreAudit::record(
                    AuditEvent::ACTION_UPDATE,
                    'hotel_reservation',
                    (int) $reservation->getKey(),
                    oldValues: $reservationOld,
                    newValues: $reservation->fresh()->toApiArray(),
                    reason: 'check-out',
                );
            }

            CoreAudit::record(
                AuditEvent::ACTION_UPDATE,
                'hotel_stay',
                (int) $stay->getKey(),
                oldValues: $old,
                newValues: $stay->fresh()->toApiArray(),
                reason: 'check-out',
            );

            return $stay->fresh();
        });
    }
}
