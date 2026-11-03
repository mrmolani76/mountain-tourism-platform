<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
    public const UPDATED_AT = null;

    protected $connection = 'tenant';

    protected $table = 'core_outlet';

    protected $fillable = ['building_id', 'name', 'outlet_type', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'created_at' => 'datetime'];

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id');
    }
}
