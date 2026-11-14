<?php

namespace Modules\Weather\App\Models;

use App\Models\BaseModel;
use Modules\Weather\App\Support\WeatherDate;

class WeatherFetchRun extends BaseModel
{
    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    public $timestamps = false;

    protected $connection = 'tenant';

    protected $table = 'weather_fetch_run';

    protected $fillable = [
        'weather_point_id', 'fetched_at', 'status', 'http_status',
        'error_message', 'duration_ms',
    ];

    protected $casts = [
        'weather_point_id' => 'integer',
        'fetched_at' => 'datetime',
        'http_status' => 'integer',
        'duration_ms' => 'integer',
    ];

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'weather_point_id' => (int) $this->getAttribute('weather_point_id'),
            'fetched_at' => WeatherDate::iso($this->getAttribute('fetched_at')),
            'status' => $this->getAttribute('status'),
            'http_status' => $this->getAttribute('http_status') === null
                ? null
                : (int) $this->getAttribute('http_status'),
            'error_message' => $this->getAttribute('error_message'),
            'duration_ms' => $this->getAttribute('duration_ms') === null
                ? null
                : (int) $this->getAttribute('duration_ms'),
        ];
    }
}
