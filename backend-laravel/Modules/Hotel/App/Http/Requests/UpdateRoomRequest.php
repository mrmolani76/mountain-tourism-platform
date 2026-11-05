<?php

namespace Modules\Hotel\App\Http\Requests;

use Modules\Hotel\App\Models\Room;

class UpdateRoomRequest extends HotelRequest
{
    public function rules(): array
    {
        return [
            'building_id' => ['sometimes', 'integer', $this->tenantExists('core_building')],
            'room_type_id' => ['sometimes', 'integer', $this->tenantExists('hotel_room_type')],
            'room_number' => ['sometimes', 'string', 'max:32'],
            'name' => ['nullable', 'string', 'max:120'],
            'accommodation_type' => ['sometimes', 'string', 'in:'.implode(',', array_keys(Room::ACCOMMODATION_TYPES))],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
