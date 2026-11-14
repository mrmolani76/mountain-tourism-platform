<?php

namespace Modules\Weather\App\Models;

use App\Models\BaseModel;
use Modules\Weather\App\Support\WeatherDate;

class WeatherForecastSnapshot extends BaseModel
{
    public $timestamps = false;

    protected $connection = 'tenant';

    protected $table = 'weather_forecast_snapshot';

    protected $fillable = [
        'weather_point_id', 'fetch_run_id', 'fetched_at', 'period_start',
        'period_end', 'raw', 'is_current',
    ];

    protected $casts = [
        'weather_point_id' => 'integer',
        'fetch_run_id' => 'integer',
        'fetched_at' => 'datetime',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'raw' => 'array',
        'is_current' => 'boolean',
    ];

    public function values()
    {
        return $this->hasMany(WeatherForecastValue::class, 'snapshot_id');
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'weather_point_id' => (int) $this->getAttribute('weather_point_id'),
            'fetch_run_id' => $this->getAttribute('fetch_run_id') === null
                ? null
                : (int) $this->getAttribute('fetch_run_id'),
            'fetched_at' => WeatherDate::iso($this->getAttribute('fetched_at')),
            'period_start' => WeatherDate::iso($this->getAttribute('period_start')),
            'period_end' => WeatherDate::iso($this->getAttribute('period_end')),
            'is_current' => (bool) $this->getAttribute('is_current'),
        ];
    }
}
