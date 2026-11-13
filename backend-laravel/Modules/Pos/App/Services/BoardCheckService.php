<?php

namespace Modules\Pos\App\Services;

use App\Support\Http\Envelope;
use Modules\Hotel\App\Models\BoardPlan;
use Modules\Hotel\App\Models\Reservation;
use Modules\Hotel\App\Models\Stay;
use Modules\Pos\App\Models\BoardClaim;

/**
 * Board meal entitlement check for hotel guests (PDF Module D acceptance).
 * Included meals must not be charged again; extras still post to folio.
 */
class BoardCheckService
{
    /**
     * @return array<string, mixed>
     */
    public function check(int $stayId, string $meal, ?string $serviceDate = null): array
    {
        if (! in_array($meal, BoardClaim::MEALS, true)) {
            Envelope::abort('invalid_meal', 'Meal must be breakfast, lunch, or dinner.', [], 400);
        }

        $stay = Stay::on(config('tenancy.connection_name'))->find($stayId);
        if ($stay === null) {
            Envelope::abort('not_found', 'Stay not found.', [], 404);
        }

        if (! $stay->isInHouse()) {
            Envelope::abort(
                'stay_not_in_house',
                'Board check only applies to in-house stays.',
                ['status' => $stay->getAttribute('status')],
                422
            );
        }

        $date = $serviceDate ?: now()->toDateString();
        $reservation = Reservation::on(config('tenancy.connection_name'))
            ->find($stay->getAttribute('reservation_id'));
        $boardPlan = $reservation?->getAttribute('board_plan_id') === null
            ? null
            : BoardPlan::on(config('tenancy.connection_name'))
                ->find((int) $reservation->getAttribute('board_plan_id'));

        $included = $boardPlan !== null && $boardPlan->includesMeal($meal);

        $existing = BoardClaim::on(config('tenancy.connection_name'))
            ->where('stay_id', $stayId)
            ->where('service_date', $date)
            ->where('meal', $meal)
            ->where('status', BoardClaim::STATUS_CLAIMED)
            ->first();

        return [
            'stay_id' => $stayId,
            'service_date' => $date,
            'meal' => $meal,
            'board_plan_id' => $boardPlan === null ? null : (int) $boardPlan->getKey(),
            'board_plan_code' => $boardPlan?->getAttribute('code'),
            'inclusions' => $boardPlan?->includedMeals() ?? [],
            'included' => $included,
            'already_claimed' => $existing !== null,
            'claim_id' => $existing === null ? null : (int) $existing->getKey(),
            'can_claim' => $included && $existing === null,
        ];
    }

    public function claim(
        int $stayId,
        string $meal,
        ?string $serviceDate = null,
        ?int $orderId = null,
    ): BoardClaim {
        $result = $this->check($stayId, $meal, $serviceDate);

        if (! $result['included']) {
            Envelope::abort(
                'meal_not_included',
                'This meal is not included in the guest board plan.',
                $result,
                422
            );
        }

        if ($result['already_claimed']) {
            Envelope::abort(
                'meal_already_claimed',
                'This board meal was already claimed for the date.',
                $result,
                422
            );
        }

        return BoardClaim::on(config('tenancy.connection_name'))->create([
            'stay_id' => $stayId,
            'order_id' => $orderId,
            'board_plan_id' => $result['board_plan_id'],
            'service_date' => $result['service_date'],
            'meal' => $meal,
            'status' => BoardClaim::STATUS_CLAIMED,
            'claimed_by' => $this->actorId(),
        ]);
    }

    private function actorId(): ?int
    {
        if (app()->bound('staff')) {
            return (int) app('staff')->getKey();
        }

        return null;
    }
}
