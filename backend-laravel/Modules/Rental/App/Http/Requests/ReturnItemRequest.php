<?php

namespace Modules\Rental\App\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Rental\App\Models\EquipmentItem;

class ReturnItemRequest extends RentalRequest
{
    public function rules(): array
    {
        return [
            'returned_at' => ['sometimes', 'date'],
            'condition_on_return' => ['sometimes', 'string', Rule::in(EquipmentItem::CONDITIONS)],
            'damage_fee_cents' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
