<?php

namespace Modules\Hotel\App\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Hotel\App\Models\BoardPlan;

class StoreBoardPlanRequest extends HotelRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', Rule::in(array_keys(BoardPlan::CODES))],
            'name' => ['required', 'string', 'max:100'],
            'price_per_person_cents' => ['required', 'integer', 'min:0'],
            'inclusions' => ['nullable', 'array'],
            'inclusions.*' => ['string', Rule::in(['breakfast', 'lunch', 'dinner'])],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
