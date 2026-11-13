<?php

namespace Modules\Rental\App\Http\Requests;

class StoreEquipmentTypeRequest extends RentalRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
