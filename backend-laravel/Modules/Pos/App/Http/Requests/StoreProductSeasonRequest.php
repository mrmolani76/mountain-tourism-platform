<?php

namespace Modules\Pos\App\Http\Requests;

class StoreProductSeasonRequest extends PosRequest
{
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', $this->tenantExists('pos_product')],
            'season_id' => ['required', 'integer', $this->tenantExists('core_season')],
            'is_available' => ['sometimes', 'boolean'],
            'price_cents' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
