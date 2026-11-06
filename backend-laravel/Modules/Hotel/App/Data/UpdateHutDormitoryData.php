<?php

namespace Modules\Hotel\App\Data;

class UpdateHutDormitoryData extends HotelData
{
    public function __construct(
        public ?int $building_id = null,
        public ?int $room_type_id = null,
        public ?string $name = null,
        public ?bool $is_active = null,
    ) {}

    public static function rules(): array
    {
        return [
            'building_id' => ['sometimes', 'integer', self::tenantExists('core_building')],
            'room_type_id' => ['nullable', 'integer', self::tenantExists('hotel_room_type')],
            'name' => ['sometimes', 'string', 'max:120'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
