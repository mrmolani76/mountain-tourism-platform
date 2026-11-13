<?php

namespace Modules\Services\App\Services;

use App\Support\Http\Envelope;
use Illuminate\Support\Facades\DB;
use Modules\Services\App\Models\Booking;
use Modules\Services\App\Models\BookingParticipant;
use Modules\Services\App\Models\Outing;
use Modules\Services\App\Models\OutingParticipant;
use Modules\Services\App\Models\Slot;

class OutingService
{
    public function openForSlot(int $slotId): Outing
    {
        $slot = Slot::on(config('tenancy.connection_name'))->find($slotId);
        if ($slot === null) {
            Envelope::abort('not_found', 'Slot not found.', [], 404);
        }

        $existing = Outing::on(config('tenancy.connection_name'))
            ->where('slot_id', $slotId)
            ->first();
        if ($existing !== null) {
            return $existing;
        }

        return DB::connection(config('tenancy.connection_name'))->transaction(function () use ($slotId) {
            $outing = Outing::on(config('tenancy.connection_name'))->create([
                'slot_id' => $slotId,
                'status' => Outing::STATUS_OPEN,
            ]);

            $bookingIds = Booking::on(config('tenancy.connection_name'))
                ->where('slot_id', $slotId)
                ->where('status', Booking::STATUS_BOOKED)
                ->pluck('id');

            $participants = BookingParticipant::on(config('tenancy.connection_name'))
                ->whereIn('booking_id', $bookingIds)
                ->get();

            foreach ($participants as $participant) {
                OutingParticipant::on(config('tenancy.connection_name'))->create([
                    'outing_id' => $outing->getKey(),
                    'booking_participant_id' => $participant->getKey(),
                    'status' => OutingParticipant::STATUS_PENDING,
                ]);
            }

            return $outing->fresh();
        });
    }

    public function markOut(OutingParticipant $row): OutingParticipant
    {
        $row->setAttribute('status', OutingParticipant::STATUS_OUT);
        $row->setAttribute('out_at', now());
        $row->save();

        $outing = Outing::on(config('tenancy.connection_name'))->find($row->getAttribute('outing_id'));
        if ($outing !== null && $outing->getAttribute('status') === Outing::STATUS_OPEN) {
            $outing->setAttribute('status', Outing::STATUS_OUT);
            $outing->setAttribute('departed_at', now());
            $outing->save();
        }

        return $row->fresh();
    }

    public function markBack(OutingParticipant $row): OutingParticipant
    {
        $row->setAttribute('status', OutingParticipant::STATUS_BACK);
        $row->setAttribute('back_at', now());
        $row->save();

        return $row->fresh();
    }

    public function close(Outing $outing): Outing
    {
        $notBack = OutingParticipant::on(config('tenancy.connection_name'))
            ->where('outing_id', $outing->getKey())
            ->whereNotIn('status', [OutingParticipant::STATUS_BACK])
            ->exists();

        if ($notBack) {
            Envelope::abort(
                'participants_not_back',
                'Cannot close outing while any participant is not back.',
                ['outing_id' => (int) $outing->getKey()],
                422
            );
        }

        $outing->setAttribute('status', Outing::STATUS_CLOSED);
        $outing->setAttribute('returned_at', now());
        $outing->save();

        return $outing->fresh();
    }
}
