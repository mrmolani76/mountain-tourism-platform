<?php

namespace Modules\Hotel\App\Http\Requests;

class UpdateRoomTypeRequest extends HotelRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'max_adults' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'max_children' => ['sometimes', 'integer', 'min:0', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
