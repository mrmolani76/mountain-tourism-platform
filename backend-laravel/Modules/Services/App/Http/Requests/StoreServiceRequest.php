<?php

namespace Modules\Services\App\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Services\App\Models\ServiceDefinition;

class StoreServiceRequest extends ServicesRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'service_type' => ['sometimes', 'string', 'max:24'],
            'description' => ['nullable', 'string'],
            'outlet_id' => ['nullable', 'integer', $this->tenantExists('core_outlet')],
            'building_id' => ['nullable', 'integer', $this->tenantExists('core_building')],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'default_capacity' => ['sometimes', 'integer', 'min:1'],
            'price_cents' => ['sometimes', 'integer', 'min:0'],
            'price_mode' => ['sometimes', 'string', Rule::in([
                ServiceDefinition::PRICE_PER_PERSON,
                ServiceDefinition::PRICE_PER_BOOKING,
                ServiceDefinition::PRICE_INCLUDED,
            ])],
            'outdoor' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
