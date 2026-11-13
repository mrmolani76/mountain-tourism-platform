<?php

namespace Modules\Services\App\Models;

use App\Models\BaseModel;
use Modules\Services\App\Support\ServicesDate;

/** Configurable bookable service (table service_service). */
class ServiceDefinition extends BaseModel
{
    public const PRICE_PER_PERSON = 'per_person';

    public const PRICE_PER_BOOKING = 'per_booking';

    public const PRICE_INCLUDED = 'included';

    protected $connection = 'tenant';

    protected $table = 'service_service';

    protected $fillable = [
        'name', 'service_type', 'description', 'outlet_id', 'building_id',
        'duration_minutes', 'default_capacity', 'price_cents', 'price_mode',
        'outdoor', 'is_active',
    ];

    protected $casts = [
        'outlet_id' => 'integer',
        'building_id' => 'integer',
        'duration_minutes' => 'integer',
        'default_capacity' => 'integer',
        'price_cents' => 'integer',
        'outdoor' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'name' => $this->getAttribute('name'),
            'service_type' => $this->getAttribute('service_type'),
            'description' => $this->getAttribute('description'),
            'outlet_id' => $this->getAttribute('outlet_id') === null ? null : (int) $this->getAttribute('outlet_id'),
            'building_id' => $this->getAttribute('building_id') === null ? null : (int) $this->getAttribute('building_id'),
            'duration_minutes' => (int) $this->getAttribute('duration_minutes'),
            'default_capacity' => (int) $this->getAttribute('default_capacity'),
            'price_cents' => (int) $this->getAttribute('price_cents'),
            'price_mode' => $this->getAttribute('price_mode'),
            'outdoor' => (bool) $this->getAttribute('outdoor'),
            'is_active' => (bool) $this->getAttribute('is_active'),
            'created_at' => ServicesDate::iso($this->getAttribute('created_at')),
            'updated_at' => ServicesDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
