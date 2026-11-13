<?php

namespace Modules\Transport\App\Http\Requests;

class StorePassengerRequest extends TransportRequest
{
    public function rules(): array
    {
        return [
            'guest_name' => ['required', 'string', 'max:160'],
            'seats' => ['sometimes', 'integer', 'min:1'],
            'stay_id' => ['nullable', $this->tenantExists('hotel_stay')],
            'reservation_id' => ['nullable', $this->tenantExists('hotel_reservation')],
            'pickup_stop_id' => ['nullable', $this->tenantExists('transport_route_stop')],
            'dropoff_stop_id' => ['nullable', $this->tenantExists('transport_route_stop')],
        ];
    }
}
