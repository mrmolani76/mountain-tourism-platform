<?php

namespace Modules\Hotel\App\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Hotel\App\Models\RoomSeasonPrice;

class UpdateRoomSeasonPriceRequest extends HotelRequest
{
    public function rules(): array
    {
        $id = (int) $this->route('room_season_price');
        $current = RoomSeasonPrice::on($this->tenantConnection())->find($id);
        $roomTypeId = (int) ($this->input('room_type_id') ?? $current?->getAttribute('room_type_id'));

        return [
            'room_type_id' => ['sometimes', 'integer', $this->tenantExists('hotel_room_type')],
            'season_id' => [
                'sometimes',
                'integer',
                $this->tenantExists('core_season'),
                Rule::unique('hotel_room_season_price', 'season_id')
                    ->where(fn ($q) => $q->where('room_type_id', $roomTypeId))
                    ->ignore($id)
                    ->connection($this->tenantConnection()),
            ],
            'amount_cents' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'season_id.unique' => 'This room type already has a price for the selected season.',
        ];
    }
}
