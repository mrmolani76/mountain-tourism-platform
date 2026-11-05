<?php

namespace Modules\Hotel\App\Models;

use App\Models\BaseModel;
use Modules\Folio\App\Models\Folio;
use Modules\Hotel\App\Support\ApiDate;

/** Active guest visit opened by check-in; owns the guest folio. */
class Stay extends BaseModel
{
    public const STATUS_IN_HOUSE = 'in_house';

    public const STATUS_CHECKED_OUT = 'checked_out';

    protected $connection = 'tenant';

    protected $table = 'hotel_stay';

    protected $fillable = [
        'reservation_id', 'folio_id', 'room_id', 'hut_bed_id',
        'checked_in_at', 'checked_out_at', 'status',
    ];

    protected $casts = [
        'reservation_id' => 'integer',
        'folio_id' => 'integer',
        'room_id' => 'integer',
        'hut_bed_id' => 'integer',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    public function folio()
    {
        return $this->belongsTo(Folio::class, 'folio_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function hutBed()
    {
        return $this->belongsTo(HutBed::class, 'hut_bed_id');
    }

    public function isInHouse(): bool
    {
        return $this->getAttribute('status') === self::STATUS_IN_HOUSE;
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'reservation_id' => $this->getAttribute('reservation_id') === null
                ? null
                : (int) $this->getAttribute('reservation_id'),
            'folio_id' => $this->getAttribute('folio_id') === null
                ? null
                : (int) $this->getAttribute('folio_id'),
            'room_id' => $this->getAttribute('room_id') === null
                ? null
                : (int) $this->getAttribute('room_id'),
            'hut_bed_id' => $this->getAttribute('hut_bed_id') === null
                ? null
                : (int) $this->getAttribute('hut_bed_id'),
            'checked_in_at' => ApiDate::iso($this->getAttribute('checked_in_at')),
            'checked_out_at' => ApiDate::iso($this->getAttribute('checked_out_at')),
            'status' => $this->getAttribute('status'),
            'created_at' => ApiDate::iso($this->getAttribute('created_at')),
            'updated_at' => ApiDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
