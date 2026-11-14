<?php

namespace Modules\Weather\App\Models;

use App\Models\BaseModel;
use Modules\Weather\App\Support\WeatherDate;

class WeatherWarningTarget extends BaseModel
{
    public const TYPE_LIFT = 'lift';

    public const TYPE_TRIP = 'trip';

    public const TYPE_SERVICE_SLOT = 'service_slot';

    public const TYPES = [
        self::TYPE_LIFT,
        self::TYPE_TRIP,
        self::TYPE_SERVICE_SLOT,
    ];

    public $timestamps = false;

    protected $connection = 'tenant';

    protected $table = 'weather_warning_target';

    protected $fillable = [
        'warning_id', 'target_type', 'target_id', 'acknowledged',
        'acknowledged_by', 'acknowledged_at',
    ];

    protected $casts = [
        'warning_id' => 'integer',
        'target_id' => 'integer',
        'acknowledged' => 'boolean',
        'acknowledged_by' => 'integer',
        'acknowledged_at' => 'datetime',
    ];

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'warning_id' => (int) $this->getAttribute('warning_id'),
            'target_type' => $this->getAttribute('target_type'),
            'target_id' => (int) $this->getAttribute('target_id'),
            'acknowledged' => (bool) $this->getAttribute('acknowledged'),
            'acknowledged_by' => $this->getAttribute('acknowledged_by') === null
                ? null
                : (int) $this->getAttribute('acknowledged_by'),
            'acknowledged_at' => WeatherDate::iso($this->getAttribute('acknowledged_at')),
        ];
    }
}
