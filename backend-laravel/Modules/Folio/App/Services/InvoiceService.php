<?php

namespace Modules\Folio\App\Services;

use Modules\Folio\App\Models\Charge;
use Modules\Folio\App\Models\Folio;
use Modules\Folio\App\Models\FolioVoid;
use Modules\Folio\App\Models\Payment;
use Modules\Folio\App\Support\FolioDate;
use Modules\Hotel\App\Models\Stay;

/**
 * Build a printable invoice JSON payload (browser print / PDF UI deferred).
 */
class InvoiceService
{
    public function __construct(
        private readonly FolioService $folios,
        private readonly ChargeToRoomService $chargeToRoom,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(Folio $folio): array
    {
        $charges = Charge::on(config('tenancy.connection_name'))
            ->where('folio_id', $folio->getKey())
            ->orderBy('id')
            ->get();

        $payments = Payment::on(config('tenancy.connection_name'))
            ->where('folio_id', $folio->getKey())
            ->orderBy('id')
            ->get();

        $voids = FolioVoid::on(config('tenancy.connection_name'))
            ->where('folio_id', $folio->getKey())
            ->orderBy('id')
            ->get();

        $activeCharges = $charges->where('is_voided', false);
        $activePayments = $payments->where('is_voided', false);

        $stay = Stay::on(config('tenancy.connection_name'))
            ->where('folio_id', $folio->getKey())
            ->orderByDesc('id')
            ->first();

        $guest = $stay === null
            ? ['guest_name' => null, 'room_number' => null, 'reservation_reference' => null]
            : $this->chargeToRoom->stayGuestContext($stay);

        return [
            'folio' => $folio->toApiArray(),
            'stay' => $stay === null ? null : array_merge($stay->toApiArray(), $guest),
            'guest_name' => $guest['guest_name'],
            'room_number' => $guest['room_number'],
            'charges' => $charges->map(fn (Charge $c) => $c->toApiArray())->all(),
            'payments' => $payments->map(fn (Payment $p) => $p->toApiArray())->all(),
            'voids' => $voids->map(fn (FolioVoid $v) => $v->toApiArray())->all(),
            'totals' => [
                'charges_cents' => (int) $activeCharges->sum('amount_cents'),
                'tax_cents' => (int) $activeCharges->sum('tax_cents'),
                'payments_cents' => (int) $activePayments->sum('amount_cents'),
                'balance_cents' => $this->folios->balanceCents($folio),
            ],
            'currency' => $folio->getAttribute('currency'),
            'generated_at' => FolioDate::iso(now()),
        ];
    }
}
