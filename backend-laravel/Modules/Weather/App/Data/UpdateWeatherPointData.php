<?php

namespace Modules\Weather\App\Data;

class UpdateWeatherPointData extends WeatherData
{
    public function __construct(
        public ?int $building_id = null,
        public ?int $lift_id = null,
        public ?string $name = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?int $altitude_m = null,
        public ?bool $is_active = null,
    ) {}

    public static function rules(): array
    {
        return [
            'building_id' => ['sometimes', 'nullable', 'integer', self::tenantExists('core_building')],
            'lift_id' => ['sometimes', 'nullable', 'integer', self::tenantExists('transport_lift')],
            'name' => ['sometimes', 'string', 'max:120'],
            'latitude' => ['sometimes', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'numeric', 'between:-180,180'],
            'altitude_m' => ['sometimes', 'nullable', 'integer'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
