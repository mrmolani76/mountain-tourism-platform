<?php

namespace Modules\Weather\App\Data;

class UpsertAvalancheData extends WeatherData
{
    public function __construct(
        public string $valid_on,
        public int $level,
        public ?string $source = null,
        public ?string $notes = null,
    ) {}

    public static function rules(): array
    {
        return [
            'valid_on' => ['required', 'date'],
            'level' => ['required', 'integer', 'min:1', 'max:5'],
            'source' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
