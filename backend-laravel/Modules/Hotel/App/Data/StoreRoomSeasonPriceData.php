<?php

namespace Modules\Hotel\App\Data;

use Illuminate\Validation\Rule;

class StoreRoomSeasonPriceData extends HotelData
{
    public function __construct(
        public int $room_type_id,
        public int $season_id,
        public int $amount_cents,
    ) {}

    public static function rules(): array
    {
        return [
            'room_type_id' => ['required', 'integer', self::tenantExists('hotel_room_type')],
            'season_id' => [
                'required',
                'integer',
                self::tenantExists('core_season'),
                Rule::unique('hotel_room_season_price', 'season_id')
                    ->where(fn ($q) => $q->where('room_type_id', (int) request()->input('room_type_id')))
                    ->connection(self::tenantConnection()),
            ],
            'amount_cents' => ['required', 'integer', 'min:0'],
        ];
    }

    public static function messages(): array
    {
        return [
            'season_id.unique' => 'This room type already has a price for the selected season.',
        ];
    }
}
