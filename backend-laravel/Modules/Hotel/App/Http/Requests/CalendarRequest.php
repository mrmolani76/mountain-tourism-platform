<?php

namespace Modules\Hotel\App\Http\Requests;

class CalendarRequest extends HotelRequest
{
    public function rules(): array
    {
        return [
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after:from'],
            'building_id' => ['nullable', 'integer', $this->tenantExists('core_building')],
        ];
    }
}
