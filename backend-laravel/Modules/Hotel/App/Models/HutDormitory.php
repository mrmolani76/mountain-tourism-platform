<?php

namespace Modules\Hotel\App\Models;

use App\Models\BaseModel;
use Modules\Core\App\Models\Building;
use Modules\Hotel\App\Support\ApiDate;

class HutDormitory extends BaseModel
{
    protected $connection = 'tenant';

    protected $table = 'hotel_hut_dormitory';

    protected $fillable = ['building_id', 'room_type_id', 'name', 'is_active'];

    protected $casts = [
        'building_id' => 'integer',
        'room_type_id' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id');
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function beds()
    {
        return $this->hasMany(HutBed::class, 'hut_dormitory_id');
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'building_id' => (int) $this->getAttribute('building_id'),
            'room_type_id' => $this->getAttribute('room_type_id') === null
                ? null
                : (int) $this->getAttribute('room_type_id'),
            'name' => $this->getAttribute('name'),
            'is_active' => (bool) $this->getAttribute('is_active'),
            'created_at' => ApiDate::iso($this->getAttribute('created_at')),
            'updated_at' => ApiDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
