<?php

namespace Modules\Hotel\App\Data;

/**
 * Query payload for room / bed availability (EQMS-style Spatie Data).
 */
class AvailabilityData extends HotelData
{
    public function __construct(
        public string $check_in_on,
        public string $check_out_on,
        public ?int $building_id = null,
    ) {}

    public static function rules(): array
    {
        return [
            'check_in_on' => ['required', 'date'],
            'check_out_on' => ['required', 'date', 'after:check_in_on'],
            'building_id' => ['nullable', 'integer', self::tenantExists('core_building')],
        ];
    }
}
