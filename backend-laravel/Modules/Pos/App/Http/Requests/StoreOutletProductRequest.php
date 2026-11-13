<?php

namespace Modules\Pos\App\Http\Requests;

class StoreOutletProductRequest extends PosRequest
{
    public function rules(): array
    {
        return [
            'outlet_id' => ['required', 'integer', $this->tenantExists('core_outlet')],
            'product_id' => ['required', 'integer', $this->tenantExists('pos_product')],
            'is_available' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
