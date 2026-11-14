<?php

namespace Modules\Weather\App\Services;

use Modules\Weather\App\Models\WeatherPoint;

class WeatherPointService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): WeatherPoint
    {
        return WeatherPoint::on(config('tenancy.connection_name'))->create([
            'building_id' => $data['building_id'] ?? null,
            'lift_id' => $data['lift_id'] ?? null,
            'name' => $data['name'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'altitude_m' => $data['altitude_m'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(WeatherPoint $point, array $data): WeatherPoint
    {
        $point->fill($data);
        $point->save();

        return $point->fresh();
    }
}
