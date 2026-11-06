<?php

namespace Modules\Hotel\App\Data;

use Illuminate\Validation\Rule;
use Modules\Hotel\App\Models\BoardPlan;

class UpdateBoardPlanData extends HotelData
{
    public function __construct(
        public ?string $code = null,
        public ?string $name = null,
        public ?int $price_per_person_cents = null,
        public ?array $inclusions = null,
        public ?bool $is_active = null,
    ) {}

    public static function rules(): array
    {
        return [
            'code' => ['sometimes', 'string', Rule::in(array_keys(BoardPlan::CODES))],
            'name' => ['sometimes', 'string', 'max:100'],
            'price_per_person_cents' => ['sometimes', 'integer', 'min:0'],
            'inclusions' => ['nullable', 'array'],
            'inclusions.*' => ['string', Rule::in(['breakfast', 'lunch', 'dinner'])],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
