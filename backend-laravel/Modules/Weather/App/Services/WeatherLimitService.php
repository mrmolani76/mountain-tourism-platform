<?php

namespace Modules\Weather\App\Services;

use Modules\Weather\App\Models\WeatherLimit;

class WeatherLimitService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): WeatherLimit
    {
        return WeatherLimit::on(config('tenancy.connection_name'))->create([
            'weather_point_id' => $data['weather_point_id'],
            'variable' => $data['variable'],
            'operator' => $data['operator'],
            'threshold' => $data['threshold'],
            'severity' => $data['severity'],
            'is_active' => $data['is_active'] ?? true,
            'created_by' => $this->actorId(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(WeatherLimit $limit, array $data): WeatherLimit
    {
        $limit->fill($data);
        $limit->save();

        return $limit->fresh();
    }

    private function actorId(): ?int
    {
        if (app()->bound('staff')) {
            return (int) app('staff')->getKey();
        }

        return null;
    }
}
