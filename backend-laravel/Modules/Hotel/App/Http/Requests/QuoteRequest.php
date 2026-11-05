<?php

namespace Modules\Hotel\App\Http\Requests;

class QuoteRequest extends HotelRequest
{
    public function rules(): array
    {
        return [
            'room_type_id' => ['required', 'integer', $this->tenantExists('hotel_room_type')],
            'board_plan_id' => ['nullable', 'integer', $this->tenantExists('hotel_board_plan')],
            'check_in_on' => ['required', 'date'],
            'check_out_on' => ['required', 'date', 'after:check_in_on'],
            'adults' => ['required', 'integer', 'min:1'],
            'children' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
