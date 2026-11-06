<?php

namespace Modules\Hotel\App\Data;

class CancelReservationData extends HotelData
{
    public function __construct(
        public ?string $reason = null,
    ) {}

    public static function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
