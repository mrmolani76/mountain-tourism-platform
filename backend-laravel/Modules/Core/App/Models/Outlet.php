<?php

namespace Modules\Core\App\Models;

use App\Models\BaseModel;

class Outlet extends BaseModel
{
    public const UPDATED_AT = null;

    /** PDF §5A / Django OutletType (16 choices). */
    public const TYPES = [
        'restaurant' => 'Restaurant',
        'self_service' => 'Self Service',
        'hut_kitchen' => 'Hut Kitchen',
        'panorama_restaurant' => 'Panorama Restaurant',
        'shop' => 'Shop',
        'rental' => 'Rental',
        'spa' => 'Spa',
        'sauna' => 'Sauna',
        'gym' => 'Gym',
        'ski_school' => 'Ski School',
        'reception' => 'Reception',
        'tickets' => 'Tickets',
        'sports' => 'Sports',
        'transport' => 'Transport',
        'lifts' => 'Lifts',
        'other' => 'Other',
    ];

    protected $connection = 'tenant';

    protected $table = 'core_outlet';

    protected $fillable = ['building_id', 'name', 'outlet_type', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'created_at' => 'datetime'];

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id');
    }
}
