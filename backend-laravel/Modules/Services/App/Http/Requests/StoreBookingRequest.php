<?php

namespace Modules\Services\App\Http\Requests;

use Illuminate\Validation\Rule;

class StoreBookingRequest extends ServicesRequest
{
    public function rules(): array
    {
        return [
            'slot_id' => ['required', 'integer', $this->tenantExists('service_slot')],
            'guest_name' => ['required', 'string', 'max:160'],
            'participants_count' => ['required', 'integer', 'min:1'],
            'participant_names' => ['sometimes', 'array'],
            'participant_names.*' => ['string', 'max:120'],
            'stay_id' => ['nullable', 'integer', $this->tenantExists('hotel_stay')],
            'pay_now' => ['sometimes', 'boolean'],
            'payment_method' => ['sometimes', 'string', Rule::in(['cash', 'card'])],
        ];
    }
}
