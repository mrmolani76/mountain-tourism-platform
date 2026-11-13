<?php

namespace Modules\Transport\App\Services;

use Modules\Transport\App\Models\Lift;
use Modules\Transport\App\Models\LiftStatusHistory;

class LiftService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Lift
    {
        $lift = Lift::on(config('tenancy.connection_name'))->create([
            'name' => $data['name'],
            'origin_building_id' => $data['origin_building_id'] ?? null,
            'destination_building_id' => $data['destination_building_id'] ?? null,
            'lift_type' => $data['lift_type'] ?? 'chairlift',
            'capacity' => $data['capacity'] ?? null,
            'opens_at' => $data['opens_at'] ?? null,
            'closes_at' => $data['closes_at'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        LiftStatusHistory::on(config('tenancy.connection_name'))->create([
            'lift_id' => $lift->getKey(),
            'status' => LiftStatusHistory::STATUS_OPEN,
            'reason' => 'Initial status',
            'changed_by' => $this->actorId(),
        ]);

        return $lift->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function changeStatus(Lift $lift, array $data): LiftStatusHistory
    {
        return LiftStatusHistory::on(config('tenancy.connection_name'))->create([
            'lift_id' => $lift->getKey(),
            'status' => $data['status'],
            'reason' => $data['reason'] ?? null,
            'changed_by' => $this->actorId(),
        ]);
    }

    /**
     * Lobby-ready lift status snapshot (Phase 4 API; lobby page Phase 5).
     *
     * @return list<array<string, mixed>>
     */
    public function lobbyStatus(): array
    {
        return Lift::on(config('tenancy.connection_name'))
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function (Lift $lift) {
                $current = $lift->currentStatus();

                return [
                    'id' => (int) $lift->getKey(),
                    'name' => $lift->getAttribute('name'),
                    'lift_type' => $lift->getAttribute('lift_type'),
                    'status' => $current?->getAttribute('status') ?? LiftStatusHistory::STATUS_CLOSED,
                    'reason' => $current?->getAttribute('reason'),
                    'updated_at' => $current?->getAttribute('created_at')?->format('Y-m-d\TH:i:s.u\Z'),
                ];
            })
            ->values()
            ->all();
    }

    private function actorId(): ?int
    {
        if (app()->bound('staff')) {
            return (int) app('staff')->getKey();
        }

        return null;
    }
}
