<?php

namespace Modules\Core\App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantSettings extends Model
{
    protected $connection = 'tenant';

    protected $table = 'core_tenant_settings';

    protected $fillable = [
        'currency', 'timezone', 'units', 'tax_rates',
    ];

    protected $casts = [
        'tax_rates' => 'array',
    ];
}
