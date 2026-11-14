<?php

namespace Modules\Weather\App\Services;

use Modules\Weather\App\Models\WeatherAvalancheDaily;

/** Avalanche bulletin stub (Phase 5 UI). Upsert by valid_on. */
class AvalancheService
{
    /**
     * @return list<WeatherAvalancheDaily>
     */
    public function list(): array
    {
        return WeatherAvalancheDaily::on(config('tenancy.connection_name'))
            ->orderByDesc('valid_on')
            ->limit(60)
            ->get()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function upsert(array $data): WeatherAvalancheDaily
    {
        $conn = config('tenancy.connection_name');
        $validOn = $data['valid_on'];

        $row = WeatherAvalancheDaily::on($conn)
            ->whereDate('valid_on', $validOn)
            ->first();

        if ($row === null) {
            return WeatherAvalancheDaily::on($conn)->create([
                'valid_on' => $validOn,
                'level' => (int) $data['level'],
                'source' => $data['source'] ?? null,
                'notes' => $data['notes'] ?? null,
                'entered_by' => $this->actorId(),
            ]);
        }

        $row->fill([
            'level' => (int) $data['level'],
            'source' => $data['source'] ?? $row->getAttribute('source'),
            'notes' => array_key_exists('notes', $data) ? $data['notes'] : $row->getAttribute('notes'),
            'entered_by' => $this->actorId(),
        ]);
        $row->save();

        return $row->fresh();
    }

    private function actorId(): ?int
    {
        if (app()->bound('staff')) {
            return (int) app('staff')->getKey();
        }

        return null;
    }
}
