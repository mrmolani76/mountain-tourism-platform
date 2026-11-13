<?php

namespace Modules\Transport\App\Models;

use App\Models\BaseModel;
use Modules\Transport\App\Support\TransportDate;

class Lift extends BaseModel
{
    public const TYPES = ['cable_car', 'chairlift', 'drag_lift', 'gondola', 'other'];

    protected $connection = 'tenant';

    protected $table = 'transport_lift';

    protected $fillable = [
        'name', 'origin_building_id', 'destination_building_id', 'lift_type',
        'capacity', 'opens_at', 'closes_at', 'is_active',
    ];

    protected $casts = [
        'origin_building_id' => 'integer',
        'destination_building_id' => 'integer',
        'capacity' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function statusHistory()
    {
        return $this->hasMany(LiftStatusHistory::class, 'lift_id')->orderByDesc('created_at');
    }

    public function currentStatus(): ?LiftStatusHistory
    {
        return LiftStatusHistory::on(config('tenancy.connection_name'))
            ->where('lift_id', $this->getKey())
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();
    }

    public function toApiArray(): array
    {
        $current = $this->currentStatus();

        return [
            'id' => (int) $this->getKey(),
            'name' => $this->getAttribute('name'),
            'origin_building_id' => $this->getAttribute('origin_building_id') === null
                ? null
                : (int) $this->getAttribute('origin_building_id'),
            'destination_building_id' => $this->getAttribute('destination_building_id') === null
                ? null
                : (int) $this->getAttribute('destination_building_id'),
            'lift_type' => $this->getAttribute('lift_type'),
            'capacity' => $this->getAttribute('capacity') === null
                ? null
                : (int) $this->getAttribute('capacity'),
            'opens_at' => $this->getAttribute('opens_at'),
            'closes_at' => $this->getAttribute('closes_at'),
            'is_active' => (bool) $this->getAttribute('is_active'),
            'current_status' => $current?->getAttribute('status'),
            'current_status_reason' => $current?->getAttribute('reason'),
            'current_status_at' => TransportDate::iso($current?->getAttribute('created_at')),
            'created_at' => TransportDate::iso($this->getAttribute('created_at')),
            'updated_at' => TransportDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
