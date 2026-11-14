<?php

namespace Modules\Weather\App\Models;

use App\Models\BaseModel;
use Modules\Weather\App\Support\WeatherDate;

class WeatherLimit extends BaseModel
{
    public const OP_GT = 'gt';

    public const OP_GTE = 'gte';

    public const OP_LT = 'lt';

    public const OP_LTE = 'lte';

    public const OPERATORS = [
        self::OP_GT,
        self::OP_GTE,
        self::OP_LT,
        self::OP_LTE,
    ];

    public const SEVERITY_INFO = 'info';

    public const SEVERITY_WARNING = 'warning';

    public const SEVERITY_DANGER = 'danger';

    public const SEVERITIES = [
        self::SEVERITY_INFO,
        self::SEVERITY_WARNING,
        self::SEVERITY_DANGER,
    ];

    protected $connection = 'tenant';

    protected $table = 'weather_limit';

    protected $fillable = [
        'weather_point_id', 'variable', 'operator', 'threshold',
        'severity', 'is_active', 'created_by',
    ];

    protected $casts = [
        'weather_point_id' => 'integer',
        'threshold' => 'float',
        'is_active' => 'boolean',
        'created_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'weather_point_id' => (int) $this->getAttribute('weather_point_id'),
            'variable' => $this->getAttribute('variable'),
            'operator' => $this->getAttribute('operator'),
            'threshold' => (float) $this->getAttribute('threshold'),
            'severity' => $this->getAttribute('severity'),
            'is_active' => (bool) $this->getAttribute('is_active'),
            'created_by' => $this->getAttribute('created_by') === null
                ? null
                : (int) $this->getAttribute('created_by'),
            'created_at' => WeatherDate::iso($this->getAttribute('created_at')),
            'updated_at' => WeatherDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
