<?php

namespace Modules\Hotel\App\Data;

/**
 * Run trigger for nightly folio posting (EQMS-style Spatie Data).
 */
class RunNightlyPostingData extends HotelData
{
    public function __construct(
        public ?string $date = null,
    ) {}

    public static function rules(): array
    {
        return [
            'date' => ['nullable', 'date'],
        ];
    }
}
