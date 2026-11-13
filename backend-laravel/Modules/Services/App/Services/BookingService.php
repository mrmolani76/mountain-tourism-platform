<?php

namespace Modules\Services\App\Services;

use App\Support\Http\Envelope;
use Illuminate\Support\Facades\DB;
use Modules\Folio\App\Models\Charge;
use Modules\Folio\App\Models\Folio;
use Modules\Folio\App\Models\Payment;
use Modules\Folio\App\Services\FolioService;
use Modules\Hotel\App\Models\Stay;
use Modules\Services\App\Models\Booking;
use Modules\Services\App\Models\BookingParticipant;
use Modules\Services\App\Models\ServiceDefinition;
use Modules\Services\App\Models\Slot;

class BookingService
{
    public function __construct(private readonly FolioService $folios)
    {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function book(array $data): Booking
    {
        $slot = Slot::on(config('tenancy.connection_name'))->find((int) $data['slot_id']);
        if ($slot === null || $slot->getAttribute('status') !== Slot::STATUS_SCHEDULED) {
            Envelope::abort('slot_unavailable', 'Slot is not available for booking.', [], 422);
        }

        $count = max(1, (int) $data['participants_count']);
        if ($count > $slot->remainingCapacity()) {
            Envelope::abort('capacity_exceeded', 'Slot capacity would be exceeded.', [
                'remaining' => $slot->remainingCapacity(),
                'requested' => $count,
            ], 422);
        }

        $service = ServiceDefinition::on(config('tenancy.connection_name'))
            ->find((int) $slot->getAttribute('service_id'));

        return DB::connection(config('tenancy.connection_name'))->transaction(function () use ($data, $slot, $count, $service) {
            $folio = $this->resolveFolio($data);
            $amount = $this->priceCents($service, $count);

            $booking = Booking::on(config('tenancy.connection_name'))->create([
                'slot_id' => $slot->getKey(),
                'stay_id' => $data['stay_id'] ?? null,
                'folio_id' => $folio?->getKey(),
                'guest_name' => $data['guest_name'],
                'participants_count' => $count,
                'status' => Booking::STATUS_BOOKED,
                'booked_by' => $this->actorId(),
            ]);

            $names = $data['participant_names'] ?? [$data['guest_name']];
            foreach (array_values($names) as $i => $name) {
                BookingParticipant::on(config('tenancy.connection_name'))->create([
                    'booking_id' => $booking->getKey(),
                    'name' => (string) $name,
                    'is_lead' => $i === 0,
                ]);
            }

            if ($amount > 0 && $folio !== null) {
                $charge = $this->folios->postCharge($folio, [
                    'outlet_id' => $service?->getAttribute('outlet_id'),
                    'description' => 'Service booking #'.$booking->getKey(),
                    'amount_cents' => $amount,
                    'tax_cents' => 0,
                    'source_type' => Charge::SOURCE_SERVICE,
                    'source_id' => (string) $booking->getKey(),
                    'stay_id' => $booking->getAttribute('stay_id'),
                ]);
                $booking->setAttribute('charge_id', $charge->getKey());
                $booking->save();

                if (($data['pay_now'] ?? false) === true) {
                    $method = ($data['payment_method'] ?? 'cash') === 'card'
                        ? Payment::METHOD_CARD
                        : Payment::METHOD_CASH;
                    $this->folios->recordPayment($folio, [
                        'method' => $method,
                        'amount_cents' => $amount,
                        'reference' => 'service-booking-'.$booking->getKey(),
                    ]);
                }
            }

            $slot->setAttribute('booked_count', (int) $slot->getAttribute('booked_count') + $count);
            $slot->save();

            return $booking->fresh();
        });
    }

    public function cancel(Booking $booking): Booking
    {
        if ($booking->getAttribute('status') !== Booking::STATUS_BOOKED) {
            Envelope::abort('not_cancellable', 'Only booked bookings can be cancelled.', [], 422);
        }

        return DB::connection(config('tenancy.connection_name'))->transaction(function () use ($booking) {
            $slot = Slot::on(config('tenancy.connection_name'))->find($booking->getAttribute('slot_id'));
            if ($slot !== null) {
                $slot->setAttribute(
                    'booked_count',
                    max(0, (int) $slot->getAttribute('booked_count') - (int) $booking->getAttribute('participants_count'))
                );
                $slot->save();
            }

            $chargeId = $booking->getAttribute('charge_id');
            $folioId = $booking->getAttribute('folio_id');
            if ($chargeId !== null && $folioId !== null) {
                $folio = Folio::on(config('tenancy.connection_name'))->find((int) $folioId);
                if ($folio !== null && $folio->isOpen()) {
                    $this->folios->voidEntry($folio, [
                        'charge_id' => (int) $chargeId,
                        'reason' => 'Service booking cancelled',
                        'reason_code' => 'cancelled',
                    ]);
                }
            }

            $booking->setAttribute('status', Booking::STATUS_CANCELLED);
            $booking->save();

            return $booking->fresh();
        });
    }

    private function priceCents(?ServiceDefinition $service, int $participants): int
    {
        if ($service === null) {
            return 0;
        }
        $mode = $service->getAttribute('price_mode');
        $unit = (int) $service->getAttribute('price_cents');
        if ($mode === ServiceDefinition::PRICE_INCLUDED) {
            return 0;
        }
        if ($mode === ServiceDefinition::PRICE_PER_BOOKING) {
            return $unit;
        }

        return $unit * $participants;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveFolio(array $data): ?Folio
    {
        if (! empty($data['stay_id'])) {
            $stay = Stay::on(config('tenancy.connection_name'))->find((int) $data['stay_id']);
            if ($stay === null || ! $stay->isInHouse()) {
                Envelope::abort('stay_not_in_house', 'Stay must be in-house.', [], 422);
            }
            $folio = Folio::on(config('tenancy.connection_name'))
                ->find((int) $stay->getAttribute('folio_id'));
            if ($folio === null || ! $folio->isOpen()) {
                Envelope::abort('folio_missing', 'Stay has no open folio.', [], 422);
            }

            return $folio;
        }

        if (($data['pay_now'] ?? false) === true) {
            return $this->folios->open('service-walk-in');
        }

        return null;
    }

    private function actorId(): ?int
    {
        if (app()->bound('staff')) {
            return (int) app('staff')->getKey();
        }

        return null;
    }
}
