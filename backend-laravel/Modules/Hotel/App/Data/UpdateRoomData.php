<?php

namespace Modules\Hotel\App\Data;

use Modules\Hotel\App\Models\Room;

class UpdateRoomData extends HotelData
{
    public function __construct(
        public ?int $building_id = null,
        public ?int $room_type_id = null,
        public ?string $room_number = null,
        public ?string $name = null,
        public ?string $accommodation_type = null,
        public ?bool $is_active = null,
    ) {}

    public static function rules(): array
    {
        return [
            'building_id' => ['sometimes', 'integer', self::tenantExists('core_building')],
            'room_type_id' => ['sometimes', 'integer', self::tenantExists('hotel_room_type')],
            'room_number' => ['sometimes', 'string', 'max:32'],
            'name' => ['nullable', 'string', 'max:120'],
            'accommodation_type' => ['sometimes', 'string', 'in:'.implode(',', array_keys(Room::ACCOMMODATION_TYPES))],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
