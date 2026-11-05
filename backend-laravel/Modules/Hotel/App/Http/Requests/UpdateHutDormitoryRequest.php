<?php

namespace Modules\Hotel\App\Http\Requests;

class UpdateHutDormitoryRequest extends HotelRequest
{
    public function rules(): array
    {
        return [
            'building_id' => ['sometimes', 'integer', $this->tenantExists('core_building')],
            'room_type_id' => ['nullable', 'integer', $this->tenantExists('hotel_room_type')],
            'name' => ['sometimes', 'string', 'max:120'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
