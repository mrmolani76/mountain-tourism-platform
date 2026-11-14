<?php

namespace Modules\Weather\App\Models;

use App\Models\BaseModel;
use Modules\Weather\App\Support\WeatherDate;

class WeatherForecastValue extends BaseModel
{
    public const VAR_TEMPERATURE_C = 'temperature_c';

    public const VAR_WIND_MS = 'wind_ms';

    public const VAR_GUST_MS = 'gust_ms';

    public const VAR_PRECIPITATION_MM = 'precipitation_mm';

    public const VAR_SYMBOL = 'symbol';

    public const VARIABLES = [
        self::VAR_TEMPERATURE_C,
        self::VAR_WIND_MS,
        self::VAR_GUST_MS,
        self::VAR_PRECIPITATION_MM,
        self::VAR_SYMBOL,
    ];

    public $timestamps = false;

    protected $connection = 'tenant';

    protected $table = 'weather_forecast_value';

    protected $fillable = [
        'snapshot_id', 'valid_at', 'variable', 'unit', 'value',
    ];

    protected $casts = [
        'snapshot_id' => 'integer',
        'valid_at' => 'datetime',
        'value' => 'float',
    ];

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'snapshot_id' => (int) $this->getAttribute('snapshot_id'),
            'valid_at' => WeatherDate::iso($this->getAttribute('valid_at')),
            'variable' => $this->getAttribute('variable'),
            'unit' => $this->getAttribute('unit'),
            'value' => (float) $this->getAttribute('value'),
        ];
    }
}
