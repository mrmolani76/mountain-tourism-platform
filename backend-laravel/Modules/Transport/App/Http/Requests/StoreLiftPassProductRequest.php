<?php

namespace Modules\Transport\App\Http\Requests;

class StoreLiftPassProductRequest extends TransportRequest
{
    public function rules(): array
    {
        return [
            'product_id' => ['required', $this->tenantExists('pos_product')],
            'lift_id' => ['nullable', $this->tenantExists('transport_lift')],
            'valid_days' => ['required', 'integer', 'min:1', 'max:365'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
