<?php

namespace Modules\Transport\App\Http\Requests;

class PickupFromReservationRequest extends TransportRequest
{
    public function rules(): array
    {
        return [
            'reservation_id' => ['required', $this->tenantExists('hotel_reservation')],
            'route_id' => ['nullable', $this->tenantExists('transport_route')],
            'vehicle_id' => ['nullable', $this->tenantExists('transport_vehicle')],
            'driver_id' => ['nullable', $this->tenantExists('transport_driver')],
            'notes' => ['nullable', 'string'],
        ];
    }
}
