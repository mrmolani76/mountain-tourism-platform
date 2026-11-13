<?php

namespace Modules\Transport\App\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Transport\App\Models\TransportRoute;

class StoreRouteRequest extends TransportRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'route_type' => ['sometimes', 'string', Rule::in(TransportRoute::TYPES)],
            'geometry' => ['nullable', 'array'],
            'is_active' => ['sometimes', 'boolean'],
            'stops' => ['sometimes', 'array'],
            'stops.*.sequence' => ['required_with:stops', 'integer', 'min:1'],
            'stops.*.name' => ['required_with:stops', 'string', 'max:120'],
            'stops.*.building_id' => ['nullable', $this->tenantExists('core_building')],
            'stops.*.latitude' => ['nullable', 'numeric'],
            'stops.*.longitude' => ['nullable', 'numeric'],
        ];
    }
}
