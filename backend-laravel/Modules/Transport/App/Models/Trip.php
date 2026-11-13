<?php

namespace Modules\Transport\App\Models;

use App\Models\BaseModel;
use Modules\Transport\App\Support\TransportDate;

class Trip extends BaseModel
{
    public const TYPE_SHUTTLE = 'shuttle';

    public const TYPE_TAXI = 'taxi';

    public const TYPE_PICKUP = 'pickup';

    public const TYPE_DROPOFF = 'dropoff';

    public const TYPES = [
        self::TYPE_SHUTTLE,
        self::TYPE_TAXI,
        self::TYPE_PICKUP,
        self::TYPE_DROPOFF,
    ];

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_RUNNING = 'running';

    public const STATUS_DONE = 'done';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_WEATHER_CANCELLED = 'weather_cancelled';

    public const STATUSES = [
        self::STATUS_SCHEDULED,
        self::STATUS_RUNNING,
        self::STATUS_DONE,
        self::STATUS_CANCELLED,
        self::STATUS_WEATHER_CANCELLED,
    ];

    public const ACTIVE_STATUSES = [
        self::STATUS_SCHEDULED,
        self::STATUS_RUNNING,
    ];

    protected $connection = 'tenant';

    protected $table = 'transport_trip';

    protected $fillable = [
        'route_id', 'vehicle_id', 'driver_id', 'trip_type', 'starts_at', 'ends_at',
        'seats', 'status', 'reservation_id', 'notes',
    ];

    protected $casts = [
        'route_id' => 'integer',
        'vehicle_id' => 'integer',
        'driver_id' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'seats' => 'integer',
        'reservation_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function passengers()
    {
        return $this->hasMany(TripPassenger::class, 'trip_id');
    }

    public function bookedSeats(): int
    {
        return (int) $this->passengers()
            ->whereIn('status', [TripPassenger::STATUS_BOOKED, TripPassenger::STATUS_BOARDED])
            ->sum('seats');
    }

    public function remainingSeats(): int
    {
        return max(0, (int) $this->getAttribute('seats') - $this->bookedSeats());
    }

    public function isActive(): bool
    {
        return in_array($this->getAttribute('status'), self::ACTIVE_STATUSES, true);
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'route_id' => $this->getAttribute('route_id') === null
                ? null
                : (int) $this->getAttribute('route_id'),
            'vehicle_id' => $this->getAttribute('vehicle_id') === null
                ? null
                : (int) $this->getAttribute('vehicle_id'),
            'driver_id' => $this->getAttribute('driver_id') === null
                ? null
                : (int) $this->getAttribute('driver_id'),
            'trip_type' => $this->getAttribute('trip_type'),
            'starts_at' => TransportDate::iso($this->getAttribute('starts_at')),
            'ends_at' => TransportDate::iso($this->getAttribute('ends_at')),
            'seats' => (int) $this->getAttribute('seats'),
            'booked_seats' => $this->bookedSeats(),
            'remaining_seats' => $this->remainingSeats(),
            'status' => $this->getAttribute('status'),
            'reservation_id' => $this->getAttribute('reservation_id') === null
                ? null
                : (int) $this->getAttribute('reservation_id'),
            'notes' => $this->getAttribute('notes'),
            'created_at' => TransportDate::iso($this->getAttribute('created_at')),
            'updated_at' => TransportDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
