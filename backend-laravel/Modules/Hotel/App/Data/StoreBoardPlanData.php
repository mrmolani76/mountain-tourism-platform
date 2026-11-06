<?php

namespace Modules\Hotel\App\Data;

use Illuminate\Validation\Rule;
use Modules\Hotel\App\Models\BoardPlan;

class StoreBoardPlanData extends HotelData
{
    public function __construct(
        public string $code,
        public string $name,
        public int $price_per_person_cents,
        public ?array $inclusions = null,
        public ?bool $is_active = null,
    ) {}

    public static function rules(): array
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
