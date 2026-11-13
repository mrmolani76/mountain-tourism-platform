<?php

namespace Modules\Services\App\Models;

use App\Models\BaseModel;
use Modules\Services\App\Support\ServicesDate;

class Slot extends BaseModel
{
    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_WEATHER_HOLD = 'weather_hold';

    protected $connection = 'tenant';

    protected $table = 'service_slot';

    protected $fillable = [
        'service_id', 'starts_at', 'ends_at', 'capacity', 'booked_count',
        'status', 'weather_warning_id',
    ];

    protected $casts = [
        'service_id' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'capacity' => 'integer',
        'booked_count' => 'integer',
        'weather_warning_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function remainingCapacity(): int
    {
        return max(0, (int) $this->getAttribute('capacity') - (int) $this->getAttribute('booked_count'));
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'service_id' => (int) $this->getAttribute('service_id'),
            'starts_at' => ServicesDate::iso($this->getAttribute('starts_at')),
            'ends_at' => ServicesDate::iso($this->getAttribute('ends_at')),
            'capacity' => (int) $this->getAttribute('capacity'),
            'booked_count' => (int) $this->getAttribute('booked_count'),
            'remaining_capacity' => $this->remainingCapacity(),
            'status' => $this->getAttribute('status'),
            'weather_warning_id' => $this->getAttribute('weather_warning_id') === null
                ? null
                : (int) $this->getAttribute('weather_warning_id'),
            'created_at' => ServicesDate::iso($this->getAttribute('created_at')),
            'updated_at' => ServicesDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
