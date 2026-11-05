<?php

namespace Modules\Folio\App\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Folio\App\Models\Payment;

class StorePaymentRequest extends FolioRequest
{
    public function rules(): array
    {
        return [
            'method' => ['required', 'string', Rule::in(Payment::METHODS)],
            'amount_cents' => ['required', 'integer', 'min:1'],
            'reference' => ['nullable', 'string', 'max:128'],
        ];
    }
}
