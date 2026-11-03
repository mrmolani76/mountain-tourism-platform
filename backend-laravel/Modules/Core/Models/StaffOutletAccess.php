<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class StaffOutletAccess extends Model
{
    public const UPDATED_AT = null;

    protected $connection = 'tenant';

    protected $table = 'core_staff_outlet_access';

    protected $fillable = ['staff_id', 'outlet_id'];

    protected $casts = ['created_at' => 'datetime'];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class, 'outlet_id');
    }
}
