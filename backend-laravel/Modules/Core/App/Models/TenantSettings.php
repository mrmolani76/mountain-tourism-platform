<?php

namespace Modules\Core\App\Models;

use App\Models\BaseModel;

class TenantSettings extends BaseModel
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
