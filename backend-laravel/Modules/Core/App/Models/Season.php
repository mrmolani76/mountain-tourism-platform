<?php

namespace Modules\Core\App\Models;

use App\Models\BaseModel;

/** DRF parity: kind + dates only (no name). */
class Season extends BaseModel
{
    protected $connection = 'tenant';

    protected $table = 'core_season';

    protected $fillable = ['kind', 'starts_on', 'ends_on', 'is_active'];

    protected $casts = [
        'starts_on' => 'date',
        'ends_on' => 'date',
        'is_active' => 'boolean',
    ];
}
