<?php

namespace Modules\Transport\App\Models;

use App\Models\BaseModel;

class RouteStop extends BaseModel
{
    protected $connection = 'tenant';

    protected $table = 'transport_route_stop';

    protected $fillable = [
        'route_id', 'sequence', 'name', 'building_id', 'latitude', 'longitude',
    ];

    protected $casts = [
        'route_id' => 'integer',
        'sequence' => 'integer',
        'building_id' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'route_id' => (int) $this->getAttribute('route_id'),
            'sequence' => (int) $this->getAttribute('sequence'),
            'name' => $this->getAttribute('name'),
            'building_id' => $this->getAttribute('building_id') === null
                ? null
                : (int) $this->getAttribute('building_id'),
            'latitude' => $this->getAttribute('latitude') === null
                ? null
                : (float) $this->getAttribute('latitude'),
            'longitude' => $this->getAttribute('longitude') === null
                ? null
                : (float) $this->getAttribute('longitude'),
        ];
    }
}
