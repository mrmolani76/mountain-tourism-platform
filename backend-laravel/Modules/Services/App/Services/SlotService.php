<?php

namespace Modules\Services\App\Services;

use App\Support\Http\Envelope;
use Illuminate\Support\Facades\DB;
use Modules\Services\App\Models\ServiceDefinition;
use Modules\Services\App\Models\Slot;
use Modules\Services\App\Models\SlotStaff;

class SlotService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Slot
    {
        $service = ServiceDefinition::on(config('tenancy.connection_name'))->find((int) $data['service_id']);
        if ($service === null || ! $service->getAttribute('is_active')) {
            Envelope::abort('service_unavailable', 'Service not found or inactive.', [], 422);
        }

        $capacity = (int) ($data['capacity'] ?? $service->getAttribute('default_capacity'));

        return Slot::on(config('tenancy.connection_name'))->create([
            'service_id' => $service->getKey(),
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'capacity' => $capacity,
            'booked_count' => 0,
            'status' => Slot::STATUS_SCHEDULED,
        ]);
    }

    /**
     * @param  list<array{staff_id:int, role?:string}>  $assignments
     */
    public function assignStaff(Slot $slot, array $assignments): array
    {
        return DB::connection(config('tenancy.connection_name'))->transaction(function () use ($slot, $assignments) {
            $created = [];
            foreach ($assignments as $row) {
                $staffId = (int) $row['staff_id'];
                $this->assertStaffFree($staffId, $slot);
                $created[] = SlotStaff::on(config('tenancy.connection_name'))->updateOrCreate(
                    ['slot_id' => $slot->getKey(), 'staff_id' => $staffId],
                    ['role' => $row['role'] ?? 'instructor']
                )->toApiArray();
            }

            return $created;
        });
    }

    public function assertStaffFree(int $staffId, Slot $slot): void
    {
        $starts = $slot->getAttribute('starts_at');
        $ends = $slot->getAttribute('ends_at');

        $overlap = SlotStaff::on(config('tenancy.connection_name'))
            ->query()
            ->where('service_slot_staff.staff_id', $staffId)
            ->join('service_slot', 'service_slot.id', '=', 'service_slot_staff.slot_id')
            ->where('service_slot.status', Slot::STATUS_SCHEDULED)
            ->where('service_slot.id', '!=', $slot->getKey())
            ->where('service_slot.starts_at', '<', $ends)
            ->where('service_slot.ends_at', '>', $starts)
            ->exists();

        if ($overlap) {
            Envelope::abort(
                'staff_overlap',
                'Staff member is already assigned to an overlapping slot.',
                ['staff_id' => $staffId],
                422
            );
        }
    }
}
