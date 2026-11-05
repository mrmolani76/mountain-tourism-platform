<?php

namespace Modules\Hotel\App\Models;

use App\Models\BaseModel;
use Modules\Hotel\App\Support\ApiDate;

/** One reserved unit per row: a room/chalet, or one individual hut bed. */
class ReservationAccommodation extends BaseModel
{
    public const TYPE_ROOM = 'room';

    public const TYPE_BED = 'bed';

    public const TYPES = [self::TYPE_ROOM, self::TYPE_BED];

    protected $connection = 'tenant';

    protected $table = 'hotel_reservation_accommodation';

    protected $fillable = [
        'reservation_id', 'accommodation_type', 'room_id', 'hut_bed_id',
        'nightly_price_cents', 'board_price_per_person_cents',
    ];

    protected $casts = [
        'reservation_id' => 'integer',
        'room_id' => 'integer',
        'hut_bed_id' => 'integer',
        'nightly_price_cents' => 'integer',
        'board_price_per_person_cents' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function hutBed()
    {
        return $this->belongsTo(HutBed::class, 'hut_bed_id');
    }

    public function unitLabel(): string
    {
        if ($this->getAttribute('accommodation_type') === self::TYPE_BED) {
            return 'Bed '.(string) $this->hutBed?->getAttribute('bed_number');
        }

        return (string) $this->room?->unitLabel();
    }

    /** Room type used for seasonal pricing (bed price comes from its dormitory). */
    public function roomType(): ?RoomType
    {
        if ($this->getAttribute('accommodation_type') === self::TYPE_BED) {
            return $this->hutBed?->dormitory?->roomType;
        }

        return $this->room?->roomType;
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'reservation_id' => (int) $this->getAttribute('reservation_id'),
            'accommodation_type' => $this->getAttribute('accommodation_type'),
            'room_id' => $this->getAttribute('room_id') === null
                ? null
                : (int) $this->getAttribute('room_id'),
            'hut_bed_id' => $this->getAttribute('hut_bed_id') === null
                ? null
                : (int) $this->getAttribute('hut_bed_id'),
            'nightly_price_cents' => (int) $this->getAttribute('nightly_price_cents'),
            'board_price_per_person_cents' => (int) $this->getAttribute('board_price_per_person_cents'),
            'created_at' => ApiDate::iso($this->getAttribute('created_at')),
            'updated_at' => ApiDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
