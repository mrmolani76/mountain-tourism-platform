<?php

namespace Modules\Folio\App\Services;

use App\Support\Http\Envelope;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\AuditEvent;
use Modules\Core\App\Support\CoreAudit;
use Modules\Folio\App\Models\Charge;
use Modules\Folio\App\Models\Folio;
use Modules\Folio\App\Models\FolioVoid;
use Modules\Folio\App\Models\Payment;

/**
 * Minimal folio money core for Module B (Module C will extend it).
 *
 * Rules: integer minor units, append-only rows, void only with reason + actor,
 * balance = active charges - active payments, checkout requires zero balance.
 */
class FolioService
{
    public function open(?string $reference = null, string $currency = 'EUR'): Folio
    {
        return Folio::on(config('tenancy.connection_name'))->create([
            'reference' => $reference,
            'status' => Folio::STATUS_OPEN,
            'currency' => $currency,
            'opened_at' => now(),
        ]);
    }

    public function postCharge(Folio $folio, array $data): Charge
    {
        $this->assertOpen($folio);
        $this->assertPositive((int) ($data['amount_cents'] ?? 0));

        $charge = Charge::on(config('tenancy.connection_name'))->create([
            'folio_id' => $folio->getKey(),
            'outlet_id' => $data['outlet_id'] ?? null,
            'posted_on' => $data['posted_on'] ?? now()->toDateString(),
            'description' => $data['description'],
            'amount_cents' => (int) $data['amount_cents'],
            'tax_cents' => (int) ($data['tax_cents'] ?? 0),
            'source_type' => $data['source_type'] ?? Charge::SOURCE_OTHER,
            'source_id' => isset($data['source_id']) ? (string) $data['source_id'] : null,
            'stay_id' => $data['stay_id'] ?? null,
            'is_voided' => false,
            'created_by' => $this->actorId(),
        ]);

        CoreAudit::record(
            AuditEvent::ACTION_CREATE,
            'folio_charge',
            (int) $charge->getKey(),
            newValues: $charge->toApiArray(),
        );

        return $charge;
    }

    public function recordPayment(Folio $folio, array $data): Payment
    {
        $this->assertOpen($folio);
        $this->assertPositive((int) ($data['amount_cents'] ?? 0));

        $payment = Payment::on(config('tenancy.connection_name'))->create([
            'folio_id' => $folio->getKey(),
            'method' => $data['method'],
            'amount_cents' => (int) $data['amount_cents'],
            'reference' => $data['reference'] ?? null,
            'is_voided' => false,
            'created_by' => $this->actorId(),
        ]);

        CoreAudit::record(
            AuditEvent::ACTION_CREATE,
            'folio_payment',
            (int) $payment->getKey(),
            newValues: $payment->toApiArray(),
        );

        return $payment;
    }

    public function voidEntry(Folio $folio, array $data): FolioVoid
    {
        $this->assertOpen($folio);

        return DB::connection(config('tenancy.connection_name'))->transaction(function () use ($folio, $data) {
            $chargeId = $data['charge_id'] ?? null;
            $paymentId = $data['payment_id'] ?? null;

            if (($chargeId === null) === ($paymentId === null)) {
                Envelope::abort('error', 'Provide exactly one of charge_id or payment_id.', [], 400);
            }

            if ($chargeId !== null) {
                $charge = Charge::on(config('tenancy.connection_name'))
                    ->where('folio_id', $folio->getKey())
                    ->find($chargeId);
                if ($charge === null) {
                    Envelope::abort('not_found', 'Charge not found on this folio.', [], 404);
                }
                if ($charge->getAttribute('is_voided')) {
                    Envelope::abort('already_voided', 'Charge is already voided.', [], 422);
                }
                $charge->setAttribute('is_voided', true);
                $charge->save();
                $entityType = 'folio_charge';
                $entityId = (int) $charge->getKey();
                $oldValues = ['amount_cents' => (int) $charge->getAttribute('amount_cents'), 'is_voided' => false];
            }
            else {
                $payment = Payment::on(config('tenancy.connection_name'))
                    ->where('folio_id', $folio->getKey())
                    ->find($paymentId);
                if ($payment === null) {
                    Envelope::abort('not_found', 'Payment not found on this folio.', [], 404);
                }
                if ($payment->getAttribute('is_voided')) {
                    Envelope::abort('already_voided', 'Payment is already voided.', [], 422);
                }
                $payment->setAttribute('is_voided', true);
                $payment->save();
                $entityType = 'folio_payment';
                $entityId = (int) $payment->getKey();
                $oldValues = ['amount_cents' => (int) $payment->getAttribute('amount_cents'), 'is_voided' => false];
            }

            $void = FolioVoid::on(config('tenancy.connection_name'))->create([
                'folio_id' => $folio->getKey(),
                'charge_id' => $chargeId,
                'payment_id' => $paymentId,
                'reason' => $data['reason'],
                'reason_code' => $data['reason_code'] ?? FolioVoid::REASON_WEATHER,
                'voided_by' => $this->actorId(),
            ]);

            CoreAudit::record(
                AuditEvent::ACTION_UPDATE,
                $entityType,
                $entityId,
                oldValues: $oldValues,
                newValues: ['is_voided' => true],
                reason: (string) $data['reason'],
            );

            return $void;
        });
    }

    public function balanceCents(Folio $folio): int
    {
        $charges = (int) Charge::on(config('tenancy.connection_name'))
            ->where('folio_id', $folio->getKey())
            ->where('is_voided', false)
            ->sum('amount_cents');

        $payments = (int) Payment::on(config('tenancy.connection_name'))
            ->where('folio_id', $folio->getKey())
            ->where('is_voided', false)
            ->sum('amount_cents');

        return $charges - $payments;
    }

    public function assertZeroBalance(Folio $folio): void
    {
        $balance = $this->balanceCents($folio);
        if ($balance !== 0) {
            Envelope::abort(
                'folio_balance_not_zero',
                'Checkout requires a zero folio balance.',
                ['balance_cents' => $balance],
                422
            );
        }
    }

    public function close(Folio $folio): Folio
    {
        $this->assertZeroBalance($folio);
        $folio->setAttribute('status', Folio::STATUS_CLOSED);
        $folio->setAttribute('closed_at', now());
        $folio->save();

        return $folio;
    }

    private function assertOpen(Folio $folio): void
    {
        if (! $folio->isOpen()) {
            Envelope::abort('folio_closed', 'Folio is closed.', [], 422);
        }
    }

    private function assertPositive(int $amountCents): void
    {
        if ($amountCents <= 0) {
            Envelope::abort('invalid_amount', 'Amount must be a positive integer in minor units.', [], 400);
        }
    }

    private function actorId(): ?int
    {
        if (app()->bound('staff')) {
            return (int) app('staff')->getKey();
        }

        return null;
    }
}
