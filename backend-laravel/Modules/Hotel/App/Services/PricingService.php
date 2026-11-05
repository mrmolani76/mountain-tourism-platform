<?php

namespace Modules\Hotel\App\Services;

use App\Support\Http\Envelope;
use Carbon\CarbonImmutable;
use Modules\Core\App\Models\Season;
use Modules\Hotel\App\Models\BoardPlan;
use Modules\Hotel\App\Models\RoomSeasonPrice;
use Modules\Hotel\App\Models\RoomType;

/**
 * Seasonal room night pricing + board per person per night (integer minor units).
 * Seasons come from Module A; a missing season/price is a blocking config error.
 */
class PricingService
{
    public function roomNightPriceForDate(RoomType $roomType, string $date): int
    {
        $season = Season::on(config('tenancy.connection_name'))
            ->where('is_active', true)
            ->where('starts_on', '<=', $date)
            ->where('ends_on', '>=', $date)
            ->orderBy('starts_on')
            ->first();

        if ($season === null) {
            Envelope::abort(
                'pricing_missing',
                'No active season covers the requested night.',
                ['date' => $date, 'room_type_id' => (int) $roomType->getKey()],
                422
            );
        }

        $price = RoomSeasonPrice::on(config('tenancy.connection_name'))
            ->where('room_type_id', $roomType->getKey())
            ->where('season_id', $season->getKey())
            ->first();

        if ($price === null) {
            Envelope::abort(
                'pricing_missing',
                'No seasonal price configured for this room type.',
                [
                    'date' => $date,
                    'season_id' => (int) $season->getKey(),
                    'room_type_id' => (int) $roomType->getKey(),
                ],
                422
            );
        }

        return (int) $price->getAttribute('amount_cents');
    }

    /** @return list<array{date: string, amount_cents: int}> */
    public function nightlyBreakdown(RoomType $roomType, string $checkInOn, string $checkOutOn): array
    {
        $rows = [];
        $cursor = CarbonImmutable::parse($checkInOn);
        $end = CarbonImmutable::parse($checkOutOn);

        while ($cursor->lessThan($end)) {
            $date = $cursor->toDateString();
            $rows[] = [
                'date' => $date,
                'amount_cents' => $this->roomNightPriceForDate($roomType, $date),
            ];
            $cursor = $cursor->addDay();
        }

        return $rows;
    }

    public function roomTotalForStay(RoomType $roomType, string $checkInOn, string $checkOutOn): int
    {
        $total = 0;
        foreach ($this->nightlyBreakdown($roomType, $checkInOn, $checkOutOn) as $row) {
            $total += $row['amount_cents'];
        }

        return $total;
    }

    public function boardTotal(?BoardPlan $boardPlan, int $nights, int $guests): int
    {
        if ($boardPlan === null || $nights < 1 || $guests < 1) {
            return 0;
        }

        return (int) $boardPlan->getAttribute('price_per_person_cents') * $nights * $guests;
    }

    /**
     * Quote payload used by the reservation form before saving.
     *
     * @return array<string, mixed>
     */
    public function quote(
        RoomType $roomType,
        ?BoardPlan $boardPlan,
        string $checkInOn,
        string $checkOutOn,
        int $adults,
        int $children,
    ): array {
        $breakdown = $this->nightlyBreakdown($roomType, $checkInOn, $checkOutOn);
        $nights = count($breakdown);
        $guests = max(0, $adults) + max(0, $children);
        $roomTotal = array_sum(array_column($breakdown, 'amount_cents'));
        $boardPerPerson = $boardPlan === null ? 0 : (int) $boardPlan->getAttribute('price_per_person_cents');
        $boardTotal = $this->boardTotal($boardPlan, $nights, $guests);

        return [
            'nights' => $nights,
            'guests' => $guests,
            'nightly' => $breakdown,
            'room_total_cents' => $roomTotal,
            'board_price_per_person_cents' => $boardPerPerson,
            'board_inclusions' => $boardPlan?->includedMeals() ?? [],
            'board_total_cents' => $boardTotal,
            'total_cents' => $roomTotal + $boardTotal,
        ];
    }
}
