<?php

namespace Modules\Rental\App\Services;

use App\Support\Http\Envelope;
use Modules\Core\App\Models\Season;
use Modules\Rental\App\Models\SeasonPrice;

class PricingService
{
    public function dayRateCents(int $equipmentTypeId, ?string $onDate = null): int
    {
        $date = $onDate ?: now()->toDateString();
        $season = Season::on(config('tenancy.connection_name'))
            ->where('is_active', true)
            ->where('starts_on', '<=', $date)
            ->where('ends_on', '>=', $date)
            ->orderByDesc('id')
            ->first();

        if ($season === null) {
            Envelope::abort('season_missing', 'No active season covers the rental date.', [
                'on_date' => $date,
            ], 422);
        }

        $price = SeasonPrice::on(config('tenancy.connection_name'))
            ->where('equipment_type_id', $equipmentTypeId)
            ->where('season_id', $season->getKey())
            ->first();

        if ($price === null) {
            Envelope::abort('rate_missing', 'No day rate for this equipment type in the current season.', [
                'equipment_type_id' => $equipmentTypeId,
                'season_id' => (int) $season->getKey(),
            ], 422);
        }

        return (int) $price->getAttribute('day_rate_cents');
    }
}
