<?php

namespace Modules\Pos\App\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Pos\App\Models\BoardClaim;

class AddOrderItemRequest extends PosRequest
{
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', $this->tenantExists('pos_product')],
            'quantity' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:255'],
            'is_board_included' => ['sometimes', 'boolean'],
            'meal' => ['required_if:is_board_included,true', 'string', Rule::in(BoardClaim::MEALS)],
            'service_date' => ['sometimes', 'date'],
        ];
    }
}
