<?php

namespace Modules\Transport\App\Models;

use App\Models\BaseModel;
use Modules\Transport\App\Support\TransportDate;

class TransportRoute extends BaseModel
{
    public const TYPES = ['shuttle', 'taxi', 'pickup', 'dropoff'];

    protected $connection = 'tenant';

    protected $table = 'transport_route';

    protected $fillable = [
        'name', 'route_type', 'geometry', 'is_active',
    ];

    protected $casts = [
        'geometry' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function stops()
    {
        return $this->hasMany(RouteStop::class, 'route_id')->orderBy('sequence');
    }

    public function toApiArray(): array
    {
        $stops = $this->relationLoaded('stops')
            ? $this->getRelation('stops')
            : null;

        return [
            'id' => (int) $this->getKey(),
            'name' => $this->getAttribute('name'),
            'route_type' => $this->getAttribute('route_type'),
            'geometry' => $this->getAttribute('geometry'),
            'is_active' => (bool) $this->getAttribute('is_active'),
            'stops' => $stops === null
                ? null
                : $stops->map(fn (RouteStop $s) => $s->toApiArray())->values()->all(),
            'created_at' => TransportDate::iso($this->getAttribute('created_at')),
            'updated_at' => TransportDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
