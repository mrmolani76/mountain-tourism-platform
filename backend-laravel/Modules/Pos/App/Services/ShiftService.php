<?php

namespace Modules\Pos\App\Services;

use App\Support\Http\Envelope;
use Modules\Pos\App\Models\CashierShift;

class ShiftService
{
    public function open(int $outletId, int $openingFloatCents = 0, ?string $notes = null): CashierShift
    {
        $existing = CashierShift::on(config('tenancy.connection_name'))
            ->where('outlet_id', $outletId)
            ->where('status', CashierShift::STATUS_OPEN)
            ->first();

        if ($existing !== null) {
            Envelope::abort(
                'shift_already_open',
                'Outlet already has an open cashier shift.',
                ['shift_id' => (int) $existing->getKey()],
                422
            );
        }

        $actor = $this->actorId();
        if ($actor === null) {
            Envelope::abort('unauthenticated', 'Staff session required.', [], 401);
        }

        return CashierShift::on(config('tenancy.connection_name'))->create([
            'outlet_id' => $outletId,
            'opened_by' => $actor,
            'opened_at' => now(),
            'opening_float_cents' => $openingFloatCents,
            'expected_cash_cents' => $openingFloatCents,
            'status' => CashierShift::STATUS_OPEN,
            'notes' => $notes,
        ]);
    }

    public function close(CashierShift $shift, int $countedCashCents, ?string $notes = null): CashierShift
    {
        if (! $shift->isOpen()) {
            Envelope::abort('shift_closed', 'Shift is already closed.', [], 422);
        }

        $expected = (int) $shift->getAttribute('expected_cash_cents');
        $shift->setAttribute('closed_by', $this->actorId());
        $shift->setAttribute('closed_at', now());
        $shift->setAttribute('counted_cash_cents', $countedCashCents);
        $shift->setAttribute('difference_cents', $countedCashCents - $expected);
        $shift->setAttribute('status', CashierShift::STATUS_CLOSED);
        if ($notes !== null) {
            $shift->setAttribute('notes', $notes);
        }
        $shift->save();

        return $shift->fresh();
    }

    public function requireOpenShift(int $outletId): CashierShift
    {
        $shift = CashierShift::on(config('tenancy.connection_name'))
            ->where('outlet_id', $outletId)
            ->where('status', CashierShift::STATUS_OPEN)
            ->first();

        if ($shift === null) {
            Envelope::abort(
                'shift_required',
                'Open a cashier shift before POS sales.',
                ['outlet_id' => $outletId],
                422
            );
        }

        return $shift;
    }

    public function addExpectedCash(CashierShift $shift, int $amountCents): void
    {
        $shift->setAttribute(
            'expected_cash_cents',
            (int) $shift->getAttribute('expected_cash_cents') + $amountCents
        );
        $shift->save();
    }

    private function actorId(): ?int
    {
        if (app()->bound('staff')) {
            return (int) app('staff')->getKey();
        }

        return null;
    }
}
