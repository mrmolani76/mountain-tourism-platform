<?php

namespace Modules\Transport\App\Models;

use App\Models\BaseModel;
use Modules\Transport\App\Support\TransportDate;

class Vehicle extends BaseModel
{
    public const TYPE_MINIBUS = 'minibus';

    public const TYPE_VAN = 'van';

    public const TYPE_CAR = 'car';

    public const TYPE_SNOWMOBILE = 'snowmobile';

    public const TYPE_OTHER = 'other';

    public const TYPES = [
        self::TYPE_MINIBUS,
        self::TYPE_VAN,
        self::TYPE_CAR,
        self::TYPE_SNOWMOBILE,
        self::TYPE_OTHER,
    ];

    protected $connection = 'tenant';

    protected $table = 'transport_vehicle';

    protected $fillable = ['name', 'plate', 'vehicle_type', 'seats', 'is_active'];

    protected $casts = [
        'seats' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'name' => $this->getAttribute('name'),
            'plate' => $this->getAttribute('plate'),
            'vehicle_type' => $this->getAttribute('vehicle_type'),
            'seats' => (int) $this->getAttribute('seats'),
            'is_active' => (bool) $this->getAttribute('is_active'),
            'created_at' => TransportDate::iso($this->getAttribute('created_at')),
            'updated_at' => TransportDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
