<?php

namespace Modules\Transport\App\Models;

use App\Models\BaseModel;
use Modules\Transport\App\Support\TransportDate;

class TripPassenger extends BaseModel
{
    public const STATUS_BOOKED = 'booked';

    public const STATUS_BOARDED = 'boarded';

    public const STATUS_NO_SHOW = 'no_show';

    public const STATUSES = [
        self::STATUS_BOOKED,
        self::STATUS_BOARDED,
        self::STATUS_NO_SHOW,
    ];

    protected $connection = 'tenant';

    protected $table = 'transport_trip_passenger';

    protected $fillable = [
        'trip_id', 'stay_id', 'reservation_id', 'guest_name', 'seats',
        'pickup_stop_id', 'dropoff_stop_id', 'status', 'charge_id',
    ];

    protected $casts = [
        'trip_id' => 'integer',
        'stay_id' => 'integer',
        'reservation_id' => 'integer',
        'seats' => 'integer',
        'pickup_stop_id' => 'integer',
        'dropoff_stop_id' => 'integer',
        'charge_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'trip_id' => (int) $this->getAttribute('trip_id'),
            'stay_id' => $this->getAttribute('stay_id') === null
                ? null
                : (int) $this->getAttribute('stay_id'),
            'reservation_id' => $this->getAttribute('reservation_id') === null
                ? null
                : (int) $this->getAttribute('reservation_id'),
            'guest_name' => $this->getAttribute('guest_name'),
            'seats' => (int) $this->getAttribute('seats'),
            'pickup_stop_id' => $this->getAttribute('pickup_stop_id') === null
                ? null
                : (int) $this->getAttribute('pickup_stop_id'),
            'dropoff_stop_id' => $this->getAttribute('dropoff_stop_id') === null
                ? null
                : (int) $this->getAttribute('dropoff_stop_id'),
            'status' => $this->getAttribute('status'),
            'charge_id' => $this->getAttribute('charge_id') === null
                ? null
                : (int) $this->getAttribute('charge_id'),
            'created_at' => TransportDate::iso($this->getAttribute('created_at')),
            'updated_at' => TransportDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
