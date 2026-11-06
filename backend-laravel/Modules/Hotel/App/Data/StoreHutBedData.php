<?php

namespace Modules\Hotel\App\Data;

class StoreHutBedData extends HotelData
{
    public function __construct(
        public int $hut_dormitory_id,
        public string $bed_number,
        public ?bool $is_active = null,
    ) {}

    public static function rules(): array
    {
        return [
            'hut_dormitory_id' => ['required', 'integer', self::tenantExists('hotel_hut_dormitory')],
            'bed_number' => ['required', 'string', 'max:32'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
