<?php

namespace Modules\Transport\App\Models;

use App\Models\BaseModel;
use Modules\Transport\App\Support\TransportDate;

class Driver extends BaseModel
{
    protected $connection = 'tenant';

    protected $table = 'transport_driver';

    protected $fillable = ['staff_id', 'name', 'phone', 'licence_no', 'is_active'];

    protected $casts = [
        'staff_id' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'staff_id' => $this->getAttribute('staff_id') === null
                ? null
                : (int) $this->getAttribute('staff_id'),
            'name' => $this->getAttribute('name'),
            'phone' => $this->getAttribute('phone'),
            'licence_no' => $this->getAttribute('licence_no'),
            'is_active' => (bool) $this->getAttribute('is_active'),
            'created_at' => TransportDate::iso($this->getAttribute('created_at')),
            'updated_at' => TransportDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
