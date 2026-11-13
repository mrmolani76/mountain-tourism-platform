<?php

namespace Modules\Transport\App\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Transport\App\Models\Vehicle;

class StoreVehicleRequest extends TransportRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'plate' => ['required', 'string', 'max:32'],
            'vehicle_type' => ['sometimes', 'string', Rule::in(Vehicle::TYPES)],
            'seats' => ['required', 'integer', 'min:1', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
