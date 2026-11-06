<?php

namespace Modules\Hotel\App\Data;

/**
 * Query payload for allocation calendar (EQMS-style Spatie Data).
 */
class CalendarData extends HotelData
{
    public function __construct(
        public string $from,
        public string $to,
        public ?int $building_id = null,
    ) {}

    public static function rules(): array
    {
        return [
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after:from'],
            'building_id' => ['nullable', 'integer', self::tenantExists('core_building')],
        ];
    }
}
