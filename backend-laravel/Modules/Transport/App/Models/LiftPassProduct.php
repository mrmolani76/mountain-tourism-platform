<?php

namespace Modules\Transport\App\Models;

use App\Models\BaseModel;
use Modules\Transport\App\Support\TransportDate;

class LiftPassProduct extends BaseModel
{
    protected $connection = 'tenant';

    protected $table = 'transport_lift_pass_product';

    protected $fillable = [
        'product_id', 'lift_id', 'valid_days', 'is_active',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'lift_id' => 'integer',
        'valid_days' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'product_id' => (int) $this->getAttribute('product_id'),
            'lift_id' => $this->getAttribute('lift_id') === null
                ? null
                : (int) $this->getAttribute('lift_id'),
            'valid_days' => (int) $this->getAttribute('valid_days'),
            'is_active' => (bool) $this->getAttribute('is_active'),
            'created_at' => TransportDate::iso($this->getAttribute('created_at')),
            'updated_at' => TransportDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
