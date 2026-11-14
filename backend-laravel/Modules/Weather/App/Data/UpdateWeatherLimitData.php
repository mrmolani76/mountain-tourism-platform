<?php

namespace Modules\Weather\App\Data;

use Illuminate\Validation\Rule;
use Modules\Weather\App\Models\WeatherForecastValue;
use Modules\Weather\App\Models\WeatherLimit;

class UpdateWeatherLimitData extends WeatherData
{
    public function __construct(
        public ?int $weather_point_id = null,
        public ?string $variable = null,
        public ?string $operator = null,
        public ?float $threshold = null,
        public ?string $severity = null,
        public ?bool $is_active = null,
    ) {}

    public static function rules(): array
    {
        return [
            'weather_point_id' => ['sometimes', 'integer', self::tenantExists('weather_point')],
            'variable' => ['sometimes', 'string', Rule::in(WeatherForecastValue::VARIABLES)],
            'operator' => ['sometimes', 'string', Rule::in(WeatherLimit::OPERATORS)],
            'threshold' => ['sometimes', 'numeric'],
            'severity' => ['sometimes', 'string', Rule::in(WeatherLimit::SEVERITIES)],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
