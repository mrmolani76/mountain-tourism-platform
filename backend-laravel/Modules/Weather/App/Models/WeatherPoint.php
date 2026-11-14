<?php

namespace Modules\Weather\App\Models;

use App\Models\BaseModel;
use Modules\Weather\App\Support\WeatherDate;

class WeatherPoint extends BaseModel
{
    protected $connection = 'tenant';

    protected $table = 'weather_point';

    protected $fillable = [
        'building_id', 'lift_id', 'name', 'latitude', 'longitude',
        'altitude_m', 'is_active',
    ];

    protected $casts = [
        'building_id' => 'integer',
        'lift_id' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'altitude_m' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function limits()
    {
        return $this->hasMany(WeatherLimit::class, 'weather_point_id');
    }

    public function currentSnapshot(): ?WeatherForecastSnapshot
    {
        return WeatherForecastSnapshot::on(config('tenancy.connection_name'))
            ->where('weather_point_id', $this->getKey())
            ->where('is_current', true)
            ->first();
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'building_id' => $this->getAttribute('building_id') === null
                ? null
                : (int) $this->getAttribute('building_id'),
            'lift_id' => $this->getAttribute('lift_id') === null
                ? null
                : (int) $this->getAttribute('lift_id'),
            'name' => $this->getAttribute('name'),
            'latitude' => (float) $this->getAttribute('latitude'),
            'longitude' => (float) $this->getAttribute('longitude'),
            'altitude_m' => $this->getAttribute('altitude_m') === null
                ? null
                : (int) $this->getAttribute('altitude_m'),
            'is_active' => (bool) $this->getAttribute('is_active'),
            'created_at' => WeatherDate::iso($this->getAttribute('created_at')),
            'updated_at' => WeatherDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
