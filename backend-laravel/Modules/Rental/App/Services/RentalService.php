<?php

namespace Modules\Rental\App\Services;

use App\Support\Http\Envelope;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Modules\Folio\App\Models\Charge;
use Modules\Folio\App\Models\Folio;
use Modules\Folio\App\Models\Payment;
use Modules\Folio\App\Services\FolioService;
use Modules\Hotel\App\Models\Stay;
use Modules\Rental\App\Models\EquipmentItem;
use Modules\Rental\App\Models\Rental;
use Modules\Rental\App\Models\RentalItem;

/**
 * Rent out / return equipment. One physical item cannot be OUT twice.
 * Late days and damage fees post as Folio charges (source_type = rental).
 */
class RentalService
{
    public function __construct(
        private readonly PricingService $pricing,
        private readonly FolioService $folios,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function rentOut(array $data): Rental
    {
        $itemIds = array_values(array_unique(array_map('intval', $data['equipment_item_ids'])));
        if ($itemIds === []) {
            Envelope::abort('items_required', 'Provide at least one equipment_item_id.', [], 400);
        }

        $days = max(1, (int) ($data['days'] ?? 1));
        $dueOn = $data['due_on'] ?? CarbonImmutable::now()->addDays($days)->toDateString();

        return DB::connection(config('tenancy.connection_name'))->transaction(function () use ($data, $itemIds, $days, $dueOn) {
            foreach ($itemIds as $itemId) {
                $this->assertItemAvailable($itemId);
            }

            $folio = $this->resolveFolio($data);
            $reference = $data['reference'] ?? ('RNT-'.strtoupper(bin2hex(random_bytes(4))));

            $rental = Rental::on(config('tenancy.connection_name'))->create([
                'reference' => $reference,
                'customer_type' => $data['customer_type'],
                'stay_id' => $data['stay_id'] ?? null,
                'folio_id' => $folio->getKey(),
                'guest_name' => $data['guest_name'],
                'rented_by' => $this->actorId(),
                'rented_at' => now(),
                'due_on' => $dueOn,
                'status' => Rental::STATUS_OPEN,
                'deposit_cents' => (int) ($data['deposit_cents'] ?? 0),
                'notes' => $data['notes'] ?? null,
            ]);

            $rentalTotal = 0;
            foreach ($itemIds as $itemId) {
                $item = EquipmentItem::on(config('tenancy.connection_name'))->find($itemId);
                if ($item === null) {
                    Envelope::abort('item_unavailable', 'Equipment item not found.', [
                        'equipment_item_id' => $itemId,
                    ], 422);
                }
                $rate = $this->pricing->dayRateCents((int) $item->getAttribute('equipment_type_id'));
                $line = $rate * $days;
                $rentalTotal += $line;

                RentalItem::on(config('tenancy.connection_name'))->create([
                    'rental_id' => $rental->getKey(),
                    'equipment_item_id' => $itemId,
                    'day_rate_cents' => $rate,
                    'days' => $days,
                    'line_total_cents' => $line,
                    'status' => RentalItem::STATUS_OUT,
                ]);
            }

            $chargeTotal = $rentalTotal + (int) $rental->getAttribute('deposit_cents');
            $charge = $this->folios->postCharge($folio, [
                'description' => 'Equipment rental '.$rental->getAttribute('reference'),
                'amount_cents' => $chargeTotal,
                'tax_cents' => 0,
                'source_type' => Charge::SOURCE_RENTAL,
                'source_id' => (string) $rental->getKey(),
                'stay_id' => $rental->getAttribute('stay_id'),
            ]);

            $rental->setAttribute('charge_id', $charge->getKey());
            $rental->save();

            if (($data['customer_type'] ?? null) === Rental::CUSTOMER_WALK_IN
                && ($data['pay_now'] ?? true)) {
                $method = ($data['payment_method'] ?? 'cash') === 'card'
                    ? Payment::METHOD_CARD
                    : Payment::METHOD_CASH;
                $this->folios->recordPayment($folio, [
                    'method' => $method,
                    'amount_cents' => $chargeTotal,
                    'reference' => 'rental-'.$rental->getAttribute('reference'),
                ]);
            }

            return $rental->fresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function returnItem(RentalItem $line, array $data): RentalItem
    {
        if (! $line->isOut()) {
            Envelope::abort('already_returned', 'Rental line is not out.', [], 422);
        }

        $rental = Rental::on(config('tenancy.connection_name'))->find($line->getAttribute('rental_id'));
        if ($rental === null || ! $rental->isOpen()) {
            Envelope::abort('rental_closed', 'Rental is not open.', [], 422);
        }

        $returnedAt = CarbonImmutable::parse($data['returned_at'] ?? now());
        $dueOn = CarbonImmutable::parse($rental->getAttribute('due_on'))->startOfDay();
        $returnDay = $returnedAt->startOfDay();
        $lateDays = $returnDay->greaterThan($dueOn)
            ? (int) $dueOn->diffInDays($returnDay)
            : 0;

        $dayRate = (int) $line->getAttribute('day_rate_cents');
        $lateFee = $lateDays * $dayRate;
        $damageFee = max(0, (int) ($data['damage_fee_cents'] ?? 0));
        $condition = $data['condition_on_return'] ?? EquipmentItem::CONDITION_OK;

        return DB::connection(config('tenancy.connection_name'))->transaction(function () use (
            $line, $rental, $returnedAt, $lateDays, $lateFee, $damageFee, $condition
        ) {
            $line->setAttribute('returned_at', $returnedAt);
            $line->setAttribute('condition_on_return', $condition);
            $line->setAttribute('late_days', $lateDays);
            $line->setAttribute('late_fee_cents', $lateFee);
            $line->setAttribute('damage_fee_cents', $damageFee);
            $line->setAttribute(
                'status',
                $damageFee > 0 || $condition === EquipmentItem::CONDITION_DAMAGED
                    ? RentalItem::STATUS_DAMAGED
                    : RentalItem::STATUS_RETURNED
            );
            $line->save();

            $item = EquipmentItem::on(config('tenancy.connection_name'))
                ->find($line->getAttribute('equipment_item_id'));
            if ($item !== null && $condition === EquipmentItem::CONDITION_DAMAGED) {
                $item->setAttribute('condition', EquipmentItem::CONDITION_DAMAGED);
                $item->save();
            }

            $folioId = $rental->getAttribute('folio_id');
            $folio = $folioId === null
                ? null
                : Folio::on(config('tenancy.connection_name'))->find((int) $folioId);

            if ($folio !== null && $folio->isOpen()) {
                if ($lateFee > 0) {
                    $this->folios->postCharge($folio, [
                        'description' => 'Late rental fee '.$rental->getAttribute('reference'),
                        'amount_cents' => $lateFee,
                        'tax_cents' => 0,
                        'source_type' => Charge::SOURCE_RENTAL,
                        'source_id' => (string) $line->getKey(),
                        'stay_id' => $rental->getAttribute('stay_id'),
                    ]);
                }
                if ($damageFee > 0) {
                    $this->folios->postCharge($folio, [
                        'description' => 'Damage fee '.$rental->getAttribute('reference'),
                        'amount_cents' => $damageFee,
                        'tax_cents' => 0,
                        'source_type' => Charge::SOURCE_RENTAL,
                        'source_id' => (string) $line->getKey(),
                        'stay_id' => $rental->getAttribute('stay_id'),
                    ]);
                }
            }

            $stillOut = RentalItem::on(config('tenancy.connection_name'))
                ->where('rental_id', $rental->getKey())
                ->where('status', RentalItem::STATUS_OUT)
                ->exists();

            if (! $stillOut) {
                $rental->setAttribute('status', Rental::STATUS_RETURNED);
                $rental->save();
            } elseif ($lateDays > 0) {
                $rental->setAttribute('status', Rental::STATUS_LATE);
                $rental->save();
            }

            return $line->fresh();
        });
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function currentlyOut(?bool $overdueOnly = false): array
    {
        $today = now()->toDateString();
        $query = RentalItem::on(config('tenancy.connection_name'))
            ->query()
            ->where('rental_rental_item.status', RentalItem::STATUS_OUT)
            ->join('rental_rental', 'rental_rental.id', '=', 'rental_rental_item.rental_id')
            ->join('rental_equipment_item', 'rental_equipment_item.id', '=', 'rental_rental_item.equipment_item_id')
            ->select([
                'rental_rental_item.*',
                'rental_rental.reference',
                'rental_rental.guest_name',
                'rental_rental.due_on',
                'rental_rental.status as rental_status',
                'rental_equipment_item.item_code',
            ])
            ->orderBy('rental_rental.due_on');

        if ($overdueOnly) {
            $query->where('rental_rental.due_on', '<', $today);
        }

        return $query->get()->map(function ($row) use ($today) {
            return [
                'rental_item_id' => (int) $row->id,
                'rental_id' => (int) $row->rental_id,
                'reference' => $row->reference,
                'guest_name' => $row->guest_name,
                'item_code' => $row->item_code,
                'equipment_item_id' => (int) $row->equipment_item_id,
                'due_on' => CarbonImmutable::parse($row->due_on)->toDateString(),
                'overdue' => CarbonImmutable::parse($row->due_on)->toDateString() < $today,
                'day_rate_cents' => (int) $row->day_rate_cents,
                'days' => (int) $row->days,
            ];
        })->all();
    }

    private function assertItemAvailable(int $equipmentItemId): void
    {
        $item = EquipmentItem::on(config('tenancy.connection_name'))->find($equipmentItemId);
        if ($item === null || ! $item->getAttribute('is_active')) {
            Envelope::abort('item_unavailable', 'Equipment item not found or inactive.', [
                'equipment_item_id' => $equipmentItemId,
            ], 422);
        }

        if ($item->getAttribute('condition') === EquipmentItem::CONDITION_RETIRED) {
            Envelope::abort('item_retired', 'Equipment item is retired.', [
                'equipment_item_id' => $equipmentItemId,
            ], 422);
        }

        $out = RentalItem::on(config('tenancy.connection_name'))
            ->where('equipment_item_id', $equipmentItemId)
            ->where('status', RentalItem::STATUS_OUT)
            ->exists();

        if ($out) {
            Envelope::abort('item_already_out', 'Equipment item is already rented out.', [
                'equipment_item_id' => $equipmentItemId,
            ], 422);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveFolio(array $data): Folio
    {
        if (($data['customer_type'] ?? null) === Rental::CUSTOMER_ROOM) {
            $stayId = $data['stay_id'] ?? null;
            if ($stayId === null) {
                Envelope::abort('stay_required', 'Room rentals require stay_id.', [], 422);
            }
            $stay = Stay::on(config('tenancy.connection_name'))->find((int) $stayId);
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

        return $this->folios->open('rental-walk-in');
    }

    private function actorId(): ?int
    {
        if (app()->bound('staff')) {
            return (int) app('staff')->getKey();
        }

        return null;
    }
}
