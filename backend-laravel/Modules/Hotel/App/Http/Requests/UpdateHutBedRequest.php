<?php

namespace Modules\Hotel\App\Http\Requests;

class UpdateHutBedRequest extends HotelRequest
{
    public function rules(): array
    {
        return [
            'hut_dormitory_id' => ['sometimes', 'integer', $this->tenantExists('hotel_hut_dormitory')],
            'bed_number' => ['sometimes', 'string', 'max:32'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
