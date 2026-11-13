<?php

namespace Modules\Rental\App\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Rental\App\Models\EquipmentItem;

class StoreEquipmentItemRequest extends RentalRequest
{
    public function rules(): array
    {
        return [
            'equipment_type_id' => ['required', 'integer', $this->tenantExists('rental_equipment_type')],
            'item_code' => ['required', 'string', 'max:32'],
            'size' => ['nullable', 'string', 'max:24'],
            'condition' => ['sometimes', 'string', Rule::in(EquipmentItem::CONDITIONS)],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
