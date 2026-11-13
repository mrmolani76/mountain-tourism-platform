<?php

namespace Modules\Transport\App\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Transport\App\Models\Trip;

class StoreTripRequest extends TransportRequest
{
    public function rules(): array
    {
        return [
            'route_id' => ['nullable', $this->tenantExists('transport_route')],
            'vehicle_id' => ['nullable', $this->tenantExists('transport_vehicle')],
            'driver_id' => ['nullable', $this->tenantExists('transport_driver')],
            'trip_type' => ['sometimes', 'string', Rule::in(Trip::TYPES)],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'seats' => ['sometimes', 'integer', 'min:1'],
            'reservation_id' => ['nullable', $this->tenantExists('hotel_reservation')],
            'notes' => ['nullable', 'string'],
        ];
    }
}
