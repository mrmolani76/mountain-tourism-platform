<?php

namespace Modules\Folio\App\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Folio\App\Models\Charge;

class StoreChargeRequest extends FolioRequest
{
    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:255'],
            'amount_cents' => ['required', 'integer', 'min:1'],
            'tax_cents' => ['sometimes', 'integer', 'min:0'],
            'posted_on' => ['sometimes', 'date'],
            'outlet_id' => ['nullable', 'integer', $this->tenantExists('core_outlet')],
            'source_type' => ['sometimes', 'string', Rule::in(Charge::SOURCES)],
            'source_id' => ['nullable', 'string', 'max:64'],
        ];
    }
}
