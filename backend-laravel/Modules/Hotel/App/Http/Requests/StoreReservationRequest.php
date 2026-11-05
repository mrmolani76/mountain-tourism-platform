<?php

namespace Modules\Hotel\App\Http\Requests;

use Modules\Hotel\App\Models\Reservation;

class StoreReservationRequest extends HotelRequest
{
    public function rules(): array
    {
        return [
            'guest_name' => ['required', 'string', 'max:160'],
            'guest_email' => ['nullable', 'email', 'max:160'],
            'guest_phone' => ['nullable', 'string', 'max:40'],
            'check_in_on' => ['required', 'date'],
            'check_out_on' => ['required', 'date', 'after:check_in_on'],
            'adults' => ['required', 'integer', 'min:1', 'max:50'],
            'children' => ['sometimes', 'integer', 'min:0', 'max:50'],
            'board_plan_id' => ['nullable', 'integer', $this->tenantExists('hotel_board_plan')],
            'deposit_cents' => ['sometimes', 'integer', 'min:0'],
            'arrival_mode' => ['nullable', 'string', 'in:'.implode(',', Reservation::ARRIVAL_MODES)],
            'arrival_time' => ['nullable', 'date_format:H:i'],
            'arrival_station' => ['nullable', 'string', 'max:160'],
            'status' => ['sometimes', 'string', 'in:'.Reservation::STATUS_PENDING.','.Reservation::STATUS_CONFIRMED],
            'notes' => ['nullable', 'string'],
            'allocations' => ['required', 'array', 'min:1', 'max:20'],
            'allocations.*.accommodation_type' => ['required', 'string', 'in:room,bed'],
            'allocations.*.room_id' => ['required_if:allocations.*.accommodation_type,room', 'integer', $this->tenantExists('hotel_room')],
            'allocations.*.hut_bed_id' => ['required_if:allocations.*.accommodation_type,bed', 'integer', $this->tenantExists('hotel_hut_bed')],
        ];
    }
}
