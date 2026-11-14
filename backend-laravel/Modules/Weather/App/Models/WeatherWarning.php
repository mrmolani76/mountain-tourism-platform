<?php

namespace Modules\Weather\App\Models;

use App\Models\BaseModel;
use Modules\Weather\App\Support\WeatherDate;

class WeatherWarning extends BaseModel
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_EXPIRED = 'expired';

    public $timestamps = false;

    protected $connection = 'tenant';

    protected $table = 'weather_warning';

    protected $fillable = [
        'weather_point_id', 'limit_id', 'forecast_value_id', 'variable',
        'threshold', 'forecast_value', 'starts_at', 'ends_at', 'status',
        'raised_at', 'resolved_at', 'resolved_by',
    ];

    protected $casts = [
        'weather_point_id' => 'integer',
        'limit_id' => 'integer',
        'forecast_value_id' => 'integer',
        'threshold' => 'float',
        'forecast_value' => 'float',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'raised_at' => 'datetime',
        'resolved_at' => 'datetime',
        'resolved_by' => 'integer',
    ];

    public function targets()
    {
        return $this->hasMany(WeatherWarningTarget::class, 'warning_id');
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'weather_point_id' => (int) $this->getAttribute('weather_point_id'),
            'limit_id' => $this->getAttribute('limit_id') === null
                ? null
                : (int) $this->getAttribute('limit_id'),
            'forecast_value_id' => $this->getAttribute('forecast_value_id') === null
                ? null
                : (int) $this->getAttribute('forecast_value_id'),
            'variable' => $this->getAttribute('variable'),
            'threshold' => (float) $this->getAttribute('threshold'),
            'forecast_value' => (float) $this->getAttribute('forecast_value'),
            'starts_at' => WeatherDate::iso($this->getAttribute('starts_at')),
            'ends_at' => WeatherDate::iso($this->getAttribute('ends_at')),
            'status' => $this->getAttribute('status'),
            'raised_at' => WeatherDate::iso($this->getAttribute('raised_at')),
            'resolved_at' => WeatherDate::iso($this->getAttribute('resolved_at')),
            'resolved_by' => $this->getAttribute('resolved_by') === null
                ? null
                : (int) $this->getAttribute('resolved_by'),
            'targets' => $this->relationLoaded('targets')
                ? $this->targets->map(fn (WeatherWarningTarget $t) => $t->toApiArray())->all()
                : null,
        ];
    }
}
