<?php

namespace Modules\Hotel\App\Http\Requests;

use Modules\Hotel\App\Models\Room;

class StoreRoomRequest extends HotelRequest
{
    public function rules(): array
    {
        return [
            'building_id' => ['required', 'integer', $this->tenantExists('core_building')],
            'room_type_id' => ['required', 'integer', $this->tenantExists('hotel_room_type')],
            'room_number' => ['required', 'string', 'max:32'],
            'name' => ['nullable', 'string', 'max:120'],
            'accommodation_type' => ['sometimes', 'string', 'in:'.implode(',', array_keys(Room::ACCOMMODATION_TYPES))],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
