<?php

namespace Modules\Pos\App\Http\Requests;

class StorePackedLunchRequest extends PosRequest
{
    public function rules(): array
    {
        return [
            'outlet_id' => ['required', 'integer', $this->tenantExists('core_outlet')],
            'stay_id' => ['nullable', 'integer', $this->tenantExists('hotel_stay')],
            'guest_name' => ['required', 'string', 'max:160'],
            'for_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'integer', $this->tenantExists('pos_product')],
            'items.*.description' => ['nullable', 'string', 'max:120'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
