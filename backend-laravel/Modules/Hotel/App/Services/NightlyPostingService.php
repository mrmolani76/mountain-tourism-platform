<?php

namespace Modules\Hotel\App\Services;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\AuditEvent;
use Modules\Core\App\Models\Outlet;
use Modules\Core\App\Support\CoreAudit;
use Modules\Folio\App\Models\Charge;
use Modules\Folio\App\Services\FolioService;
use Modules\Hotel\App\Models\NightlyPosting;
use Modules\Hotel\App\Models\Reservation;
use Modules\Hotel\App\Models\ReservationAccommodation;
use Modules\Hotel\App\Models\Stay;

/**
 * Automatic nightly posting: room nights + board, once per stay per night.
 * The unique (stay_id, posted_on) index makes retries idempotent.
 */
class NightlyPostingService
{
    public function __construct(
        private readonly PricingService $pricing,
        private readonly FolioService $folios,
    ) {
    }

    /**
     * @return array{date: string, stays_considered: int, posted: int, skipped: int, failed: int}
     */
    public function run(?string $date = null): array
    {
        $postedOn = $date ?? now()->toDateString();
        $summary = [
            'date' => $postedOn,
            'stays_considered' => 0,
            'posted' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];

        $stays = Stay::on(config('tenancy.connection_name'))
            ->where('status', Stay::STATUS_IN_HOUSE)
            ->with(['reservation.boardPlan', 'reservation.allocations.room.roomType', 'reservation.allocations.hutBed.dormitory.roomType'])
            ->get();

        foreach ($stays as $stay) {
            $reservation = $stay->reservation;
            if ($reservation === null) {
                $summary['failed']++;
                continue;
            }

            $checkIn = $reservation->getAttribute('check_in_on')->toDateString();
            $checkOut = $reservation->getAttribute('check_out_on')->toDateString();

            if ($postedOn < $checkIn || $postedOn >= $checkOut) {
                $summary['skipped']++;
                continue;
            }

            $summary['stays_considered']++;
            $result = $this->postNight($stay, $postedOn);

            match ($result->getAttribute('status')) {
                NightlyPosting::STATUS_POSTED => $summary['posted']++,
                NightlyPosting::STATUS_FAILED => $summary['failed']++,
                default => $summary['skipped']++,
            };
        }

        return $summary;
    }

    public function postNight(Stay $stay, string $date): NightlyPosting
    {
        $existing = NightlyPosting::on(config('tenancy.connection_name'))
            ->where('stay_id', $stay->getKey())
            ->whereDate('posted_on', $date)
            ->first();

        if ($existing !== null && $existing->getAttribute('status') !== NightlyPosting::STATUS_FAILED) {
            return $existing;
        }

        $reservation = $stay->reservation;
        if ($reservation === null) {
            return $this->record($stay, $date, NightlyPosting::STATUS_FAILED, 'Stay has no reservation.', 0, 0, 0, $existing);
        }

        try {
            $roomRows = $this->resolveRoomRows($reservation, $date);
        }
        catch (\Throwable) {
            return $this->record($stay, $date, NightlyPosting::STATUS_FAILED, 'Room night pricing missing.', 0, 0, 0, $existing);
        }

        $roomRows = array_map(
            fn (array $row): array => $row + [
                'outlet_id' => $this->outletIdForAllocation($row['allocation']),
            ],
            $roomRows
        );

        foreach ($roomRows as $row) {
            if ($row['outlet_id'] === null) {
                return $this->record(
                    $stay,
                    $date,
                    NightlyPosting::STATUS_FAILED,
                    'No active lodging outlet is configured for an accommodation building.',
                    0,
                    0,
                    0,
                    $existing
                );
            }
        }

        $folio = \Modules\Folio\App\Models\Folio::on(config('tenancy.connection_name'))
            ->find((int) $stay->getAttribute('folio_id'));

        if ($folio === null) {
            return $this->record($stay, $date, NightlyPosting::STATUS_FAILED, 'Stay has no folio.', 0, 0, 0, $existing);
        }

        $boardPlan = $reservation->boardPlan;
        $guests = $reservation->guestCount();
        $boardAmount = $boardPlan === null || $guests < 1
            ? 0
            : (int) $boardPlan->getAttribute('price_per_person_cents') * $guests;
        $boardOutletId = $roomRows[0]['outlet_id'] ?? null;
        if ($boardAmount > 0 && $boardOutletId === null) {
            return $this->record(
                $stay,
                $date,
                NightlyPosting::STATUS_FAILED,
                'No active lodging outlet is configured for the board charge.',
                0,
                0,
                0,
                $existing
            );
        }

        try {
            return DB::connection(config('tenancy.connection_name'))->transaction(function () use (
                $stay,
                $reservation,
                $date,
                $roomRows,
                $boardPlan,
                $boardAmount,
                $boardOutletId,
                $folio,
                $existing
            ) {
                $roomTotal = 0;
                $charges = 0;

                foreach ($roomRows as $row) {
                    /** @var ReservationAccommodation $allocation */
                    $allocation = $row['allocation'];
                    $amount = (int) $row['amount_cents'];
                    $roomTotal += $amount;
                    $charges++;

                    $this->folios->postCharge(
                        $folio,
                        [
                            'outlet_id' => $row['outlet_id'],
                            'posted_on' => $date,
                            'description' => sprintf('Room night %s %s', $allocation->unitLabel(), $date),
                            'amount_cents' => $amount,
                            'source_type' => Charge::SOURCE_ROOM_NIGHT,
                            'source_id' => (string) $allocation->getKey(),
                            'stay_id' => (int) $stay->getKey(),
                        ]
                    );
                }

                if ($boardAmount > 0 && $boardPlan !== null) {
                    $charges++;
                    $this->folios->postCharge(
                        $folio,
                        [
                            'outlet_id' => $boardOutletId,
                            'posted_on' => $date,
                            'description' => sprintf('Board %s %s', $boardPlan->getAttribute('code'), $date),
                            'amount_cents' => $boardAmount,
                            'source_type' => Charge::SOURCE_BOARD,
                            'source_id' => (string) $reservation->getKey(),
                            'stay_id' => (int) $stay->getKey(),
                        ]
                    );
                }

                return $this->record(
                    $stay,
                    $date,
                    NightlyPosting::STATUS_POSTED,
                    null,
                    $charges,
                    $roomTotal,
                    $boardAmount,
                    $existing
                );
            });
        }
        catch (QueryException) {
            $row = NightlyPosting::on(config('tenancy.connection_name'))
                ->where('stay_id', $stay->getKey())
                ->whereDate('posted_on', $date)
                ->first();

            return $row ?? $this->record($stay, $date, NightlyPosting::STATUS_FAILED, 'Posting conflict.', 0, 0, 0, $existing);
        }
    }

    /**
     * @return list<array{allocation: ReservationAccommodation, amount_cents: int}>
     */
    private function resolveRoomRows(Reservation $reservation, string $date): array
    {
        $rows = [];

        foreach ($reservation->allocations->sortBy(fn (ReservationAccommodation $allocation) => (int) $allocation->getKey()) as $allocation) {
            $roomType = $allocation->roomType();
            if ($roomType === null) {
                throw new \RuntimeException('Allocation has no room type.');
            }
            $rows[] = [
                'allocation' => $allocation,
                'amount_cents' => $this->pricing->roomNightPriceForDate($roomType, $date),
            ];
        }

        return $rows;
    }

    private function outletIdForAllocation(ReservationAccommodation $allocation): ?int
    {
        if ($allocation->getAttribute('accommodation_type') === ReservationAccommodation::TYPE_BED) {
            $buildingId = $allocation->hutBed?->dormitory?->getAttribute('building_id');
            $outletType = 'hut_kitchen';
        } else {
            $buildingId = $allocation->room?->getAttribute('building_id');
            $outletType = 'reception';
        }

        if ($buildingId === null) {
            return null;
        }

        $outletId = Outlet::on(config('tenancy.connection_name'))
            ->where('building_id', (int) $buildingId)
            ->where('outlet_type', $outletType)
            ->where('is_active', true)
            ->orderBy('id')
            ->value('id');

        return $outletId === null ? null : (int) $outletId;
    }

    private function record(
        Stay $stay,
        string $date,
        string $status,
        ?string $message,
        int $charges,
        int $roomCents,
        int $boardCents,
        ?NightlyPosting $existing = null,
    ): NightlyPosting {
        $oldValues = $existing?->toApiArray();
        $values = [
            'stay_id' => $stay->getKey(),
            'posted_on' => $date,
            'charges_count' => $charges,
            'room_charge_cents' => $roomCents,
            'board_charge_cents' => $boardCents,
            'status' => $status,
            'message' => $message,
        ];

        if ($existing === null) {
            $posting = NightlyPosting::on(config('tenancy.connection_name'))->create($values);
        } else {
            $existing->fill($values)->save();
            $posting = $existing;
        }

        if ($status === NightlyPosting::STATUS_POSTED) {
            CoreAudit::record(
                $existing === null ? AuditEvent::ACTION_CREATE : AuditEvent::ACTION_UPDATE,
                'hotel_nightly_posting',
                (int) $posting->getKey(),
                oldValues: $oldValues,
                newValues: $posting->toApiArray(),
            );
        }

        return $posting;
    }
}
