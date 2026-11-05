<?php

namespace Modules\Hotel\App\Models;

use App\Models\BaseModel;
use Modules\Core\App\Models\Building;
use Modules\Hotel\App\Support\ApiDate;

class Room extends BaseModel
{
    public const TYPE_ROOM = 'room';

    public const TYPE_CHALET = 'chalet';

    public const ACCOMMODATION_TYPES = [
        self::TYPE_ROOM => 'Room',
        self::TYPE_CHALET => 'Chalet',
    ];

    protected $connection = 'tenant';

    protected $table = 'hotel_room';

    protected $fillable = [
        'building_id', 'room_type_id', 'room_number', 'name', 'accommodation_type', 'is_active',
    ];

    protected $casts = [
        'building_id' => 'integer',
        'room_type_id' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $room) {
            if (! $room->getAttribute('accommodation_type')) {
                $room->setAttribute('accommodation_type', self::TYPE_ROOM);
            }
        });
    }

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id');
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function unitLabel(): string
    {
        return (string) ($this->getAttribute('name') ?: $this->getAttribute('room_number'));
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'building_id' => (int) $this->getAttribute('building_id'),
            'room_type_id' => (int) $this->getAttribute('room_type_id'),
            'room_number' => $this->getAttribute('room_number'),
            'name' => $this->getAttribute('name'),
            'accommodation_type' => $this->getAttribute('accommodation_type'),
            'is_active' => (bool) $this->getAttribute('is_active'),
            'created_at' => ApiDate::iso($this->getAttribute('created_at')),
            'updated_at' => ApiDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
