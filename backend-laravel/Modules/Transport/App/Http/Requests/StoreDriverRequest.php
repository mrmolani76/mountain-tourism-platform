<?php

namespace Modules\Transport\App\Http\Requests;

class StoreDriverRequest extends TransportRequest
{
    public function rules(): array
    {
        return [
            'staff_id' => ['nullable', $this->tenantExists('core_staff')],
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'licence_no' => ['nullable', 'string', 'max:64'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
