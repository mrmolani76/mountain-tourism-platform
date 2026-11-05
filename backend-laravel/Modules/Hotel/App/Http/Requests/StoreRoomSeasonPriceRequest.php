<?php

namespace Modules\Hotel\App\Http\Requests;

use Illuminate\Validation\Rule;

class StoreRoomSeasonPriceRequest extends HotelRequest
{
    public function rules(): array
    {
        return [
            'room_type_id' => ['required', 'integer', $this->tenantExists('hotel_room_type')],
            'season_id' => [
                'required',
                'integer',
                $this->tenantExists('core_season'),
                Rule::unique('hotel_room_season_price', 'season_id')
                    ->where(fn ($q) => $q->where('room_type_id', (int) $this->input('room_type_id')))
                    ->connection($this->tenantConnection()),
            ],
            'amount_cents' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'season_id.unique' => 'This room type already has a price for the selected season.',
        ];
    }
}
