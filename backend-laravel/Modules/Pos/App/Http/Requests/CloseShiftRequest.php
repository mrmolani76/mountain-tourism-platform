<?php

namespace Modules\Pos\App\Http\Requests;

class CloseShiftRequest extends PosRequest
{
    public function rules(): array
    {
        return [
            'counted_cash_cents' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
