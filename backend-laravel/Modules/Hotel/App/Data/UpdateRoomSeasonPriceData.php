<?php

namespace Modules\Hotel\App\Data;

use Illuminate\Validation\Rule;
use Modules\Hotel\App\Models\RoomSeasonPrice;

class UpdateRoomSeasonPriceData extends HotelData
{
    public function __construct(
        public ?int $room_type_id = null,
        public ?int $season_id = null,
        public ?int $amount_cents = null,
    ) {}

    public static function rules(): array
    {
        $id = (int) request()->route('room_season_price');
        $current = RoomSeasonPrice::on(self::tenantConnection())->find($id);
        $roomTypeId = (int) (request()->input('room_type_id') ?? $current?->getAttribute('room_type_id'));

        return [
            'room_type_id' => ['sometimes', 'integer', self::tenantExists('hotel_room_type')],
            'season_id' => [
                'sometimes',
                'integer',
                self::tenantExists('core_season'),
                Rule::unique('hotel_room_season_price', 'season_id')
                    ->where(fn ($q) => $q->where('room_type_id', $roomTypeId))
                    ->ignore($id)
                    ->connection(self::tenantConnection()),
            ],
            'amount_cents' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public static function messages(): array
    {
        return [
            'season_id.unique' => 'This room type already has a price for the selected season.',
        ];
    }
}
