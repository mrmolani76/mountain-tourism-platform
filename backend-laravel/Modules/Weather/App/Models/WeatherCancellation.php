<?php

namespace Modules\Weather\App\Models;

use App\Models\BaseModel;
use Modules\Weather\App\Support\WeatherDate;

class WeatherCancellation extends BaseModel
{
    public $timestamps = false;

    protected $connection = 'tenant';

    protected $table = 'weather_cancellation';

    protected $fillable = [
        'warning_id', 'target_type', 'target_id', 'cancelled_at',
        'cancelled_by', 'reason', 'notes',
    ];

    protected $casts = [
        'warning_id' => 'integer',
        'target_id' => 'integer',
        'cancelled_at' => 'datetime',
        'cancelled_by' => 'integer',
    ];

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'warning_id' => $this->getAttribute('warning_id') === null
                ? null
                : (int) $this->getAttribute('warning_id'),
            'target_type' => $this->getAttribute('target_type'),
            'target_id' => (int) $this->getAttribute('target_id'),
            'cancelled_at' => WeatherDate::iso($this->getAttribute('cancelled_at')),
            'cancelled_by' => $this->getAttribute('cancelled_by') === null
                ? null
                : (int) $this->getAttribute('cancelled_by'),
            'reason' => $this->getAttribute('reason'),
            'notes' => $this->getAttribute('notes'),
        ];
    }
}
