<?php

namespace Modules\Hotel\App\Data;

class UpdateHutBedData extends HotelData
{
    public function __construct(
        public ?int $hut_dormitory_id = null,
        public ?string $bed_number = null,
        public ?bool $is_active = null,
    ) {}

    public static function rules(): array
    {
        return [
            'hut_dormitory_id' => ['sometimes', 'integer', self::tenantExists('hotel_hut_dormitory')],
            'bed_number' => ['sometimes', 'string', 'max:32'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
