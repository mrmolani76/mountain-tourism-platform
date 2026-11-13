<?php

namespace Modules\Transport\App\Http\Requests;

use Illuminate\Validation\Rule;

class SellLiftPassRequest extends TransportRequest
{
    public function rules(): array
    {
        return [
            'lift_pass_product_id' => ['required', $this->tenantExists('transport_lift_pass_product')],
            'guest_name' => ['required', 'string', 'max:160'],
            'stay_id' => ['nullable', $this->tenantExists('hotel_stay')],
            'valid_from' => ['sometimes', 'date'],
            'pay_now' => ['sometimes', 'boolean'],
            'payment_method' => ['sometimes', 'string', Rule::in(['cash', 'card'])],
        ];
    }
}
