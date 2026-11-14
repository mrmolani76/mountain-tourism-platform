<?php

namespace Modules\Weather\App\Data;

class StoreWeatherPointData extends WeatherData
{
    public function __construct(
        public string $name,
        public float $latitude,
        public float $longitude,
        public ?int $building_id = null,
        public ?int $lift_id = null,
        public ?int $altitude_m = null,
        public ?bool $is_active = null,
    ) {}

    public static function rules(): array
    {
        return [
            'building_id' => ['nullable', 'integer', self::tenantExists('core_building')],
            'lift_id' => ['nullable', 'integer', self::tenantExists('transport_lift')],
            'name' => ['required', 'string', 'max:120'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'altitude_m' => ['nullable', 'integer'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
