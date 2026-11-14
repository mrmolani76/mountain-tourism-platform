<?php

namespace Modules\Weather\App\Services;

use Modules\Weather\App\Models\WeatherWarning;

class WeatherWarningService
{
    /**
     * @return list<WeatherWarning>
     */
    public function list(?string $status = WeatherWarning::STATUS_ACTIVE): array
    {
        $query = WeatherWarning::on(config('tenancy.connection_name'))
            ->with('targets')
            ->orderByDesc('raised_at')
            ->orderByDesc('id');

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        return $query->get()->all();
    }

    public function resolve(WeatherWarning $warning): WeatherWarning
    {
        $warning->setAttribute('status', WeatherWarning::STATUS_RESOLVED);
        $warning->setAttribute('resolved_at', now());
        $warning->setAttribute('resolved_by', $this->actorId());
        $warning->save();

        return $warning->fresh()->load('targets');
    }

    private function actorId(): ?int
    {
        if (app()->bound('staff')) {
            return (int) app('staff')->getKey();
        }

        return null;
    }
}
