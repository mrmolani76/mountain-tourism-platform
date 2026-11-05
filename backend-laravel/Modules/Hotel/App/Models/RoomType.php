<?php

namespace Modules\Hotel\App\Models;

use App\Models\BaseModel;
use Modules\Hotel\App\Support\ApiDate;

class RoomType extends BaseModel
{
    protected $connection = 'tenant';

    protected $table = 'hotel_room_type';

    protected $fillable = [
        'name', 'description', 'max_adults', 'max_children', 'is_active',
    ];

    protected $casts = [
        'max_adults' => 'integer',
        'max_children' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class, 'room_type_id');
    }

    public function seasonPrices()
    {
        return $this->hasMany(RoomSeasonPrice::class, 'room_type_id');
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'name' => $this->getAttribute('name'),
            'description' => $this->getAttribute('description'),
            'max_adults' => (int) $this->getAttribute('max_adults'),
            'max_children' => (int) $this->getAttribute('max_children'),
            'is_active' => (bool) $this->getAttribute('is_active'),
            'created_at' => ApiDate::iso($this->getAttribute('created_at')),
            'updated_at' => ApiDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
