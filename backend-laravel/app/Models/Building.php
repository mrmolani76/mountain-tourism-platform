<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Building (core_building — audit §3). Tenant connection.
 * Default ordering parity with Django Meta: ['altitude_m', 'name'] and
 * PostgreSQL NULLS FIRST semantics.
 */
class Building extends Model
{
    protected $connection = 'tenant';

    protected $table = 'core_building';

    public const TYPES = [
        'valley_station' => 'Valley Station',
        'main_hotel' => 'Main Hotel',
        'chalets' => 'Chalets',
        'mid_station' => 'Mid Station',
        'mountain_hut' => 'Mountain Hut',
        'summit_station' => 'Summit Station',
        'other' => 'Other',
    ];

    protected $fillable = [
        'name', 'building_type', 'altitude_m', 'latitude', 'longitude', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'altitude_m' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * DRF DateTimeField ISO_8601 parity: `2026-11-03T10:20:30.123456Z`.
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d\TH:i:s.u\Z');
    }

    public static function scopeDjangoOrder($query): mixed
    {
        return $query->orderByRaw('altitude_m asc nulls first')->orderBy('name');
    }
}
