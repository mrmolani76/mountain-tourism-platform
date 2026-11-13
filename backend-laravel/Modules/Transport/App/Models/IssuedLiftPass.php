<?php

namespace Modules\Transport\App\Models;

use App\Models\BaseModel;
use Modules\Transport\App\Support\TransportDate;

class IssuedLiftPass extends BaseModel
{
    public const UPDATED_AT = null;

    protected $connection = 'tenant';

    protected $table = 'transport_issued_lift_pass';

    protected $fillable = [
        'lift_pass_product_id', 'stay_id', 'guest_name', 'valid_from', 'valid_to',
        'charge_id', 'issued_by',
    ];

    protected $casts = [
        'lift_pass_product_id' => 'integer',
        'stay_id' => 'integer',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'charge_id' => 'integer',
        'issued_by' => 'integer',
        'created_at' => 'datetime',
    ];

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'lift_pass_product_id' => (int) $this->getAttribute('lift_pass_product_id'),
            'stay_id' => $this->getAttribute('stay_id') === null
                ? null
                : (int) $this->getAttribute('stay_id'),
            'guest_name' => $this->getAttribute('guest_name'),
            'valid_from' => TransportDate::date($this->getAttribute('valid_from')),
            'valid_to' => TransportDate::date($this->getAttribute('valid_to')),
            'charge_id' => $this->getAttribute('charge_id') === null
                ? null
                : (int) $this->getAttribute('charge_id'),
            'issued_by' => $this->getAttribute('issued_by') === null
                ? null
                : (int) $this->getAttribute('issued_by'),
            'created_at' => TransportDate::iso($this->getAttribute('created_at')),
        ];
    }
}
