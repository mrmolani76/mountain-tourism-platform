<?php

namespace Modules\Hotel\App\Http\Requests;

class CancelReservationRequest extends HotelRequest
{
    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
