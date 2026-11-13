<?php

namespace Modules\Transport\App\Models;

use App\Models\BaseModel;
use Modules\Transport\App\Support\TransportDate;

class RouteDefinition extends BaseModel
{
    public const TYPE_SHUTTLE = 'shuttle';

    public const TYPE_TAXI = 'taxi';

    public const TYPE_PICKUP = 'pickup';

    public const TYPE_DROPOFF = 'dropoff';

    public const TYPES = [
        self::TYPE_SHUTTLE,
        self::TYPE_TAXI,
        self::TYPE_PICKUP,
        self::TYPE_DROPOFF,
    ];

    protected $connection = 'tenant';

    protected $table = 'transport_route';

    protected $fillable = ['name', 'route_type', 'geometry', 'is_active'];

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
            : $this->stops()->get();

        return [
            'id' => (int) $this->getKey(),
            'name' => $this->getAttribute('name'),
            'route_type' => $this->getAttribute('route_type'),
            'geometry' => $this->getAttribute('geometry'),
            'is_active' => (bool) $this->getAttribute('is_active'),
            'stops' => $stops->map(fn (RouteStop $s) => $s->toApiArray())->values()->all(),
            'created_at' => TransportDate::iso($this->getAttribute('created_at')),
            'updated_at' => TransportDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
