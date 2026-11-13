<?php

namespace Modules\Pos\App\Http\Requests;

class OpenOrderRequest extends PosRequest
{
    public function rules(): array
    {
        return [
            'outlet_id' => ['required', 'integer', $this->tenantExists('core_outlet')],
            'stay_id' => ['nullable', 'integer', $this->tenantExists('hotel_stay')],
            'table_label' => ['nullable', 'string', 'max:16'],
        ];
    }
}
