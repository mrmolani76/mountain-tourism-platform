<?php

namespace Modules\Rental\App\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Rental\App\Models\Rental;

class RentOutRequest extends RentalRequest
{
    public function rules(): array
    {
        return [
            'customer_type' => ['required', 'string', Rule::in([Rental::CUSTOMER_ROOM, Rental::CUSTOMER_WALK_IN])],
            'stay_id' => ['required_if:customer_type,room', 'nullable', 'integer', $this->tenantExists('hotel_stay')],
            'guest_name' => ['required', 'string', 'max:160'],
            'equipment_item_ids' => ['required', 'array', 'min:1'],
            'equipment_item_ids.*' => ['integer', $this->tenantExists('rental_equipment_item')],
            'days' => ['sometimes', 'integer', 'min:1'],
            'due_on' => ['sometimes', 'date'],
            'deposit_cents' => ['sometimes', 'integer', 'min:0'],
            'pay_now' => ['sometimes', 'boolean'],
            'payment_method' => ['sometimes', 'string', Rule::in(['cash', 'card'])],
            'notes' => ['nullable', 'string'],
            'reference' => ['nullable', 'string', 'max:32'],
        ];
    }
}
