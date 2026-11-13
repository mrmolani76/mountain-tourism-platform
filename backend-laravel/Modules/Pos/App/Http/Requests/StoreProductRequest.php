<?php

namespace Modules\Pos\App\Http\Requests;

class StoreProductRequest extends PosRequest
{
    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'integer', $this->tenantExists('pos_category')],
            'code' => ['nullable', 'string', 'max:32'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'price_cents' => ['required', 'integer', 'min:0'],
            'tax_rate_bp' => ['sometimes', 'integer', 'min:0', 'max:10000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
