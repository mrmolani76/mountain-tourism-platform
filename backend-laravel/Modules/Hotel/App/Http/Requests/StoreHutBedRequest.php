<?php

namespace Modules\Hotel\App\Http\Requests;

class StoreHutBedRequest extends HotelRequest
{
    public function rules(): array
    {
        return [
            'hut_dormitory_id' => ['required', 'integer', $this->tenantExists('hotel_hut_dormitory')],
            'bed_number' => ['required', 'string', 'max:32'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
