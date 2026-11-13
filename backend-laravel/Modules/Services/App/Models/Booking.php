<?php

namespace Modules\Services\App\Models;

use App\Models\BaseModel;
use Modules\Services\App\Support\ServicesDate;

class Booking extends BaseModel
{
    public const STATUS_BOOKED = 'booked';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_NO_SHOW = 'no_show';

    public const STATUS_COMPLETED = 'completed';

    protected $connection = 'tenant';

    protected $table = 'service_booking';

    protected $fillable = [
        'slot_id', 'stay_id', 'folio_id', 'guest_name', 'participants_count',
        'status', 'charge_id', 'booked_by',
    ];

    protected $casts = [
        'slot_id' => 'integer',
        'stay_id' => 'integer',
        'folio_id' => 'integer',
        'participants_count' => 'integer',
        'charge_id' => 'integer',
        'booked_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function participants()
    {
        return $this->hasMany(BookingParticipant::class, 'booking_id');
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'slot_id' => (int) $this->getAttribute('slot_id'),
            'stay_id' => $this->getAttribute('stay_id') === null ? null : (int) $this->getAttribute('stay_id'),
            'folio_id' => $this->getAttribute('folio_id') === null ? null : (int) $this->getAttribute('folio_id'),
            'guest_name' => $this->getAttribute('guest_name'),
            'participants_count' => (int) $this->getAttribute('participants_count'),
            'status' => $this->getAttribute('status'),
            'charge_id' => $this->getAttribute('charge_id') === null ? null : (int) $this->getAttribute('charge_id'),
            'booked_by' => $this->getAttribute('booked_by') === null ? null : (int) $this->getAttribute('booked_by'),
            'created_at' => ServicesDate::iso($this->getAttribute('created_at')),
            'updated_at' => ServicesDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
