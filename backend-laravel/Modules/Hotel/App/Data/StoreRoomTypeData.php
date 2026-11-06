<?php

namespace Modules\Hotel\App\Data;

class StoreRoomTypeData extends HotelData
{
    public function __construct(
        public string $name,
        public ?string $description = null,
        public ?int $max_adults = null,
        public ?int $max_children = null,
        public ?bool $is_active = null,
    ) {}

    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'max_adults' => ['nullable', 'integer', 'min:1', 'max:50'],
            'max_children' => ['nullable', 'integer', 'min:0', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
