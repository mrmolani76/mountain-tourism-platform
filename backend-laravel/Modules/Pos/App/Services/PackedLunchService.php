<?php

namespace Modules\Pos\App\Services;

use App\Support\Http\Envelope;
use Illuminate\Support\Facades\DB;
use Modules\Pos\App\Models\PackedLunch;
use Modules\Pos\App\Models\PackedLunchItem;
use Modules\Pos\App\Models\Product;

class PackedLunchService
{
    public function create(array $data): PackedLunch
    {
        return DB::connection(config('tenancy.connection_name'))->transaction(function () use ($data) {
            $lunch = PackedLunch::on(config('tenancy.connection_name'))->create([
                'outlet_id' => (int) $data['outlet_id'],
                'stay_id' => $data['stay_id'] ?? null,
                'guest_name' => $data['guest_name'],
                'for_date' => $data['for_date'],
                'status' => PackedLunch::STATUS_REQUESTED,
                'notes' => $data['notes'] ?? null,
                'created_by' => $this->actorId(),
            ]);

            foreach ($data['items'] as $row) {
                $product = isset($row['product_id'])
                    ? Product::on(config('tenancy.connection_name'))->find((int) $row['product_id'])
                    : null;

                PackedLunchItem::on(config('tenancy.connection_name'))->create([
                    'packed_lunch_id' => $lunch->getKey(),
                    'product_id' => $product?->getKey(),
                    'description' => $row['description']
                        ?? $product?->getAttribute('name')
                        ?? 'Packed item',
                    'quantity' => (int) $row['quantity'],
                ]);
            }

            return $lunch->fresh();
        });
    }

    public function updateStatus(PackedLunch $lunch, string $status): PackedLunch
    {
        if (! in_array($status, PackedLunch::STATUSES, true)) {
            Envelope::abort('invalid_status', 'Invalid packed lunch status.', [], 400);
        }

        $lunch->setAttribute('status', $status);
        $lunch->save();

        return $lunch->fresh();
    }

    /**
     * Morning kitchen list for a date.
     *
     * @return list<array<string, mixed>>
     */
    public function kitchenList(string $forDate, ?int $outletId = null): array
    {
        $query = PackedLunch::on(config('tenancy.connection_name'))
            ->where('for_date', $forDate)
            ->whereIn('status', [
                PackedLunch::STATUS_REQUESTED,
                PackedLunch::STATUS_PREPARED,
            ])
            ->orderBy('id');

        if ($outletId !== null) {
            $query->where('outlet_id', $outletId);
        }

        return $query->get()->map(function (PackedLunch $lunch) {
            $payload = $lunch->toApiArray();
            $payload['items'] = PackedLunchItem::on(config('tenancy.connection_name'))
                ->where('packed_lunch_id', $lunch->getKey())
                ->get()
                ->map(fn (PackedLunchItem $i) => $i->toApiArray())
                ->all();

            return $payload;
        })->all();
    }

    private function actorId(): ?int
    {
        if (app()->bound('staff')) {
            return (int) app('staff')->getKey();
        }

        return null;
    }
}
