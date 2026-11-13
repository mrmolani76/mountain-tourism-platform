<?php

namespace Modules\Services\App\Models;

use App\Models\BaseModel;
use Modules\Services\App\Support\ServicesDate;

class ScheduleRule extends BaseModel
{
    public $timestamps = false;

    protected $connection = 'tenant';

    protected $table = 'service_schedule_rule';

    protected $fillable = [
        'service_id', 'weekday', 'starts_at', 'ends_at', 'capacity',
        'valid_from', 'valid_to', 'is_active',
    ];

    protected $casts = [
        'service_id' => 'integer',
        'weekday' => 'integer',
        'capacity' => 'integer',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'is_active' => 'boolean',
    ];

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'service_id' => (int) $this->getAttribute('service_id'),
            'weekday' => (int) $this->getAttribute('weekday'),
            'starts_at' => (string) $this->getAttribute('starts_at'),
            'ends_at' => (string) $this->getAttribute('ends_at'),
            'capacity' => (int) $this->getAttribute('capacity'),
            'valid_from' => ServicesDate::date($this->getAttribute('valid_from')),
            'valid_to' => ServicesDate::date($this->getAttribute('valid_to')),
            'is_active' => (bool) $this->getAttribute('is_active'),
        ];
    }
}
