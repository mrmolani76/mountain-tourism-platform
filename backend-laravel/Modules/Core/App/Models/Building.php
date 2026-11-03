<?php

namespace Modules\Core\App\Models;

use App\Models\BaseModel;

class Building extends BaseModel
{
    /** PDF §2 / DRF BuildingType */
    public const TYPES = [
        'valley_station' => 'Valley Station',
        'main_hotel' => 'Main Hotel',
        'chalets' => 'Chalets',
        'mid_station' => 'Mid Station',
        'mountain_hut' => 'Mountain Hut',
        'summit_station' => 'Summit Station',
        'other' => 'Other',
    ];

    protected $connection = 'tenant';

    protected $table = 'core_building';

    protected $fillable = [
        'name', 'building_type', 'altitude_m', 'latitude', 'longitude', 'is_active',
    ];

    protected $casts = [
        'altitude_m' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'is_active' => 'boolean',
    ];

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d\TH:i:s.u\Z');
    }

    public function outlets()
    {
        return $this->hasMany(Outlet::class, 'building_id');
    }
}
