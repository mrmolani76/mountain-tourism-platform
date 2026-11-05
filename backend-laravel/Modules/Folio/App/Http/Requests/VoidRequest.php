<?php

namespace Modules\Folio\App\Http\Requests;

class VoidRequest extends FolioRequest
{
    public function rules(): array
    {
        return [
            'charge_id' => ['nullable', 'integer'],
            'payment_id' => ['nullable', 'integer'],
            'reason' => ['required', 'string', 'max:255'],
            'reason_code' => ['sometimes', 'string', 'max:32'],
        ];
    }
}
