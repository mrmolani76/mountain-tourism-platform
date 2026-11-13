<?php

namespace Modules\Services\App\Http\Requests;

class StoreSlotRequest extends ServicesRequest
{
    public function rules(): array
    {
        return [
            'service_id' => ['required', 'integer', $this->tenantExists('service_service')],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'capacity' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
