<?php

namespace Modules\Rental\App\Http\Requests;

class StoreSeasonPriceRequest extends RentalRequest
{
    public function rules(): array
    {
        return [
            'equipment_type_id' => ['required', 'integer', $this->tenantExists('rental_equipment_type')],
            'season_id' => ['required', 'integer', $this->tenantExists('core_season')],
            'day_rate_cents' => ['required', 'integer', 'min:0'],
        ];
    }
}
