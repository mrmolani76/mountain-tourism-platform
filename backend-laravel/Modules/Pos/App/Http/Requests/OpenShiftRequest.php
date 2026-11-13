<?php

namespace Modules\Pos\App\Http\Requests;

class OpenShiftRequest extends PosRequest
{
    public function rules(): array
    {
        return [
            'outlet_id' => ['required', 'integer', $this->tenantExists('core_outlet')],
            'opening_float_cents' => ['sometimes', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
