<?php

namespace Modules\Hotel\App\Data;

class StoreHutDormitoryData extends HotelData
{
    public function __construct(
        public int $building_id,
        public string $name,
        public ?int $room_type_id = null,
        public ?bool $is_active = null,
    ) {}

    public static function rules(): array
    {
        return [
            'building_id' => ['required', 'integer', self::tenantExists('core_building')],
            'room_type_id' => ['nullable', 'integer', self::tenantExists('hotel_room_type')],
            'name' => ['required', 'string', 'max:120'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
