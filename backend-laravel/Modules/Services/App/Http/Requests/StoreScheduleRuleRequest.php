<?php

namespace Modules\Services\App\Http\Requests;

class StoreScheduleRuleRequest extends ServicesRequest
{
    public function rules(): array
    {
        return [
            'service_id' => ['required', 'integer', $this->tenantExists('service_service')],
            'weekday' => ['required', 'integer', 'min:0', 'max:6'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i'],
            'capacity' => ['required', 'integer', 'min:1'],
            'valid_from' => ['required', 'date'],
            'valid_to' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
