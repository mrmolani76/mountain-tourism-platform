<?php

namespace Modules\Hotel\App\Data;

/**
 * Query payload for stay price quote (EQMS-style Spatie Data).
 */
class QuoteData extends HotelData
{
    public function __construct(
        public int $room_type_id,
        public string $check_in_on,
        public string $check_out_on,
        public int $adults,
        public ?int $board_plan_id = null,
        public ?int $children = null,
    ) {}

    public static function rules(): array
    {
        return [
            'room_type_id' => ['required', 'integer', self::tenantExists('hotel_room_type')],
            'board_plan_id' => ['nullable', 'integer', self::tenantExists('hotel_board_plan')],
            'check_in_on' => ['required', 'date'],
            'check_out_on' => ['required', 'date', 'after:check_in_on'],
            'adults' => ['required', 'integer', 'min:1'],
            'children' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
