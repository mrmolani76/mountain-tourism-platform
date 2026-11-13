<?php

namespace Modules\Transport\App\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Transport\App\Models\Lift;

class StoreLiftRequest extends TransportRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'origin_building_id' => ['nullable', $this->tenantExists('core_building')],
            'destination_building_id' => ['nullable', $this->tenantExists('core_building')],
            'lift_type' => ['sometimes', 'string', Rule::in(Lift::TYPES)],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'opens_at' => ['nullable', 'date_format:H:i'],
            'closes_at' => ['nullable', 'date_format:H:i'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
