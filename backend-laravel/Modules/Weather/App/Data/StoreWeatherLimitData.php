<?php

namespace Modules\Weather\App\Data;

use Illuminate\Validation\Rule;
use Modules\Weather\App\Models\WeatherForecastValue;
use Modules\Weather\App\Models\WeatherLimit;

class StoreWeatherLimitData extends WeatherData
{
    public function __construct(
        public int $weather_point_id,
        public string $variable,
        public string $operator,
        public float $threshold,
        public string $severity,
        public ?bool $is_active = null,
    ) {}

    public static function rules(): array
    {
        return [
            'weather_point_id' => ['required', 'integer', self::tenantExists('weather_point')],
            'variable' => ['required', 'string', Rule::in(WeatherForecastValue::VARIABLES)],
            'operator' => ['required', 'string', Rule::in(WeatherLimit::OPERATORS)],
            'threshold' => ['required', 'numeric'],
            'severity' => ['required', 'string', Rule::in(WeatherLimit::SEVERITIES)],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
