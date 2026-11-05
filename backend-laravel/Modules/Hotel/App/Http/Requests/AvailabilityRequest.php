<?php

namespace Modules\Hotel\App\Http\Requests;

class AvailabilityRequest extends HotelRequest
{
    public function rules(): array
    {
        return [
            'check_in_on' => ['required', 'date'],
            'check_out_on' => ['required', 'date', 'after:check_in_on'],
            'building_id' => ['nullable', 'integer', $this->tenantExists('core_building')],
        ];
    }
}
