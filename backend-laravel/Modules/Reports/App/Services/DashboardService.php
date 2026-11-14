<?php

namespace Modules\Reports\App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Core\App\Models\Building;
use Modules\Core\App\Models\Outlet;
use Modules\Core\App\Models\TenantSettings;
use Modules\Folio\App\Models\Charge;
use Modules\Folio\App\Models\Folio;
use Modules\Folio\App\Models\Payment;
use Modules\Hotel\App\Models\Reservation;
use Modules\Hotel\App\Models\Room;
use Modules\Hotel\App\Models\Stay;
use Modules\Hotel\App\Support\ApiDate;
use Modules\Rental\App\Models\Rental;
use Modules\Rental\App\Models\RentalItem;
use Modules\Rental\App\Support\RentalDate;
use Modules\Weather\App\Models\WeatherWarning;
use Modules\Weather\App\Support\WeatherDate;

/**
 * Owner "today" dashboard aggregates. Tenant today = now()->toDateString().
 * Schema::hasTable guards so missing B–H tables do not crash.
 */
class DashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function today(): array
    {
        $today = now()->toDateString();
        $conn = config('tenancy.connection_name');

        $rentalsOut = $this->rentalsOut($conn);

        return [
            'date' => $today,
            'currency' => $this->currency($conn),
            'occupancy_by_building' => ['results' => $this->occupancyByBuilding($conn)],
            'arrivals_today' => ['results' => $this->arrivalsToday($conn, $today)],
            'departures_today' => ['results' => $this->departuresToday($conn, $today)],
            'revenue_by_outlet_today' => $this->revenueByOutletToday($conn, $today),
            'rentals_out' => ['results' => $rentalsOut, 'count' => count($rentalsOut)],
            'open_folios' => $this->openFolios($conn),
            'lift_status' => ['results' => $this->liftStatus($conn)],
            'weather_warnings' => ['results' => $this->weatherWarnings($conn)],
        ];
    }

    /**
     * @return list<array{building_id: int, building_name: string, rooms_total: int, occupied: int}>
     */
    private function occupancyByBuilding(string $conn): array
    {
        if (! Schema::connection($conn)->hasTable('core_building')) {
            return [];
        }

        $buildings = Building::on($conn)->orderBy('name')->get(['id', 'name']);
        $roomsByBuilding = [];
        $occupiedByBuilding = [];

        if (Schema::connection($conn)->hasTable('hotel_room')) {
            $roomsByBuilding = Room::on($conn)
                ->select('building_id', DB::raw('COUNT(*) as rooms_total'))
                ->groupBy('building_id')
                ->pluck('rooms_total', 'building_id')
                ->all();
        }

        if (
            Schema::connection($conn)->hasTable('hotel_stay')
            && Schema::connection($conn)->hasTable('hotel_room')
        ) {
            $occupiedByBuilding = Stay::on($conn)
                ->where('hotel_stay.status', Stay::STATUS_IN_HOUSE)
                ->whereNotNull('hotel_stay.room_id')
                ->join('hotel_room', 'hotel_room.id', '=', 'hotel_stay.room_id')
                ->select('hotel_room.building_id', DB::raw('COUNT(DISTINCT hotel_stay.room_id) as occupied'))
                ->groupBy('hotel_room.building_id')
                ->pluck('occupied', 'building_id')
                ->all();
        } elseif (Schema::connection($conn)->hasTable('hotel_stay')) {
            // No room join: fall back to in-house stay counts (not per building).
            $stayCount = (int) Stay::on($conn)->where('status', Stay::STATUS_IN_HOUSE)->count();
            if ($buildings->isEmpty()) {
                return [[
                    'building_id' => 0,
                    'building_name' => 'All',
                    'rooms_total' => 0,
                    'occupied' => $stayCount,
                ]];
            }
        }

        $out = [];
        foreach ($buildings as $building) {
            $id = (int) $building->getKey();
            $out[] = [
                'building_id' => $id,
                'building_name' => (string) $building->getAttribute('name'),
                'rooms_total' => (int) ($roomsByBuilding[$id] ?? 0),
                'occupied' => (int) ($occupiedByBuilding[$id] ?? 0),
            ];
        }

        return $out;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function arrivalsToday(string $conn, string $today): array
    {
        if (! Schema::connection($conn)->hasTable('hotel_reservation')) {
            return [];
        }

        return Reservation::on($conn)
            ->whereDate('check_in_on', $today)
            ->whereIn('status', Reservation::CHECK_IN_STATUSES)
            ->orderBy('arrival_time')
            ->get()
            ->map(fn (Reservation $r) => [
                'id' => (int) $r->getKey(),
                'reference' => $r->getAttribute('reference'),
                'guest_name' => $r->getAttribute('guest_name'),
                'check_in_on' => ApiDate::date($r->getAttribute('check_in_on')),
                'status' => $r->getAttribute('status'),
                'arrival_mode' => $r->getAttribute('arrival_mode'),
                'arrival_time' => $r->getAttribute('arrival_time'),
                'arrival_station' => $r->getAttribute('arrival_station'),
            ])
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function departuresToday(string $conn, string $today): array
    {
        if (! Schema::connection($conn)->hasTable('hotel_reservation')) {
            return [];
        }

        return Reservation::on($conn)
            ->whereDate('check_out_on', $today)
            ->whereNotIn('status', [Reservation::STATUS_CANCELLED, Reservation::STATUS_NO_SHOW])
            ->orderBy('guest_name')
            ->get()
            ->map(fn (Reservation $r) => [
                'id' => (int) $r->getKey(),
                'reference' => $r->getAttribute('reference'),
                'guest_name' => $r->getAttribute('guest_name'),
                'check_out_on' => ApiDate::date($r->getAttribute('check_out_on')),
                'status' => $r->getAttribute('status'),
            ])
            ->all();
    }

    /**
     * @return array{results: list<array{outlet_id: int|null, label: string, amount_cents: int}>, total_cents: int}
     */
    private function revenueByOutletToday(string $conn, string $today): array
    {
        $results = [];
        $total = 0;

        if (! Schema::connection($conn)->hasTable('folio_charge')) {
            return ['results' => $results, 'total_cents' => 0];
        }

        $grouped = Charge::on($conn)
            ->where('is_voided', false)
            ->whereDate('posted_on', $today)
            ->select('outlet_id', DB::raw('SUM(amount_cents) as amount_cents'))
            ->groupBy('outlet_id')
            ->get();

        $outletNames = [];
        if (Schema::connection($conn)->hasTable('core_outlet')) {
            $ids = $grouped->pluck('outlet_id')->filter()->map(fn ($id) => (int) $id)->all();
            if ($ids !== []) {
                $outletNames = Outlet::on($conn)->whereIn('id', $ids)->pluck('name', 'id')->all();
            }
        }

        foreach ($grouped as $row) {
            $outletId = $row->outlet_id === null ? null : (int) $row->outlet_id;
            $amount = (int) $row->amount_cents;
            $total += $amount;
            $results[] = [
                'outlet_id' => $outletId,
                'label' => $outletId === null
                    ? 'No outlet'
                    : (string) ($outletNames[$outletId] ?? ('Outlet #'.$outletId)),
                'amount_cents' => $amount,
            ];
        }

        return ['results' => $results, 'total_cents' => $total];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function rentalsOut(string $conn): array
    {
        $results = [];

        if (Schema::connection($conn)->hasTable('rental_rental') && class_exists(Rental::class)) {
            $rentals = Rental::on($conn)
                ->whereIn('status', [Rental::STATUS_OPEN, Rental::STATUS_LATE])
                ->orderByDesc('rented_at')
                ->limit(100)
                ->get();

            foreach ($rentals as $rental) {
                $results[] = [
                    'id' => (int) $rental->getKey(),
                    'reference' => $rental->getAttribute('reference'),
                    'guest_name' => $rental->getAttribute('guest_name'),
                    'status' => $rental->getAttribute('status'),
                    'due_on' => RentalDate::date($rental->getAttribute('due_on')),
                ];
            }

            return $results;
        }

        if (Schema::connection($conn)->hasTable('rental_rental_item') && class_exists(RentalItem::class)) {
            $items = RentalItem::on($conn)
                ->where('status', RentalItem::STATUS_OUT)
                ->orderByDesc('id')
                ->limit(100)
                ->get();

            foreach ($items as $item) {
                $results[] = [
                    'id' => (int) $item->getKey(),
                    'rental_id' => (int) $item->getAttribute('rental_id'),
                    'equipment_item_id' => (int) $item->getAttribute('equipment_item_id'),
                    'status' => $item->getAttribute('status'),
                ];
            }
        }

        return $results;
    }

    /**
     * @return array{count: int, balance_cents: int|null}
     */
    private function openFolios(string $conn): array
    {
        if (! Schema::connection($conn)->hasTable('folio_folio')) {
            return ['count' => 0, 'balance_cents' => null];
        }

        $openIds = Folio::on($conn)
            ->where('status', Folio::STATUS_OPEN)
            ->pluck('id');

        $count = $openIds->count();
        if ($count === 0) {
            return ['count' => 0, 'balance_cents' => 0];
        }

        if (
            ! Schema::connection($conn)->hasTable('folio_charge')
            || ! Schema::connection($conn)->hasTable('folio_payment')
        ) {
            return ['count' => $count, 'balance_cents' => null];
        }

        $charges = (int) Charge::on($conn)
            ->whereIn('folio_id', $openIds)
            ->where('is_voided', false)
            ->sum('amount_cents');

        $payments = (int) Payment::on($conn)
            ->whereIn('folio_id', $openIds)
            ->where('is_voided', false)
            ->sum('amount_cents');

        return [
            'count' => $count,
            'balance_cents' => $charges - $payments,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function liftStatus(string $conn): array
    {
        if (! Schema::connection($conn)->hasTable('transport_lift')) {
            return [];
        }

        $lifts = DB::connection($conn)
            ->table('transport_lift')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'lift_type']);

        $latestStatus = [];
        if (Schema::connection($conn)->hasTable('transport_lift_status_history')) {
            $history = DB::connection($conn)
                ->table('transport_lift_status_history')
                ->orderByDesc('id')
                ->get(['lift_id', 'status', 'reason', 'created_at']);

            foreach ($history as $row) {
                $liftId = (int) $row->lift_id;
                if (! isset($latestStatus[$liftId])) {
                    $latestStatus[$liftId] = [
                        'status' => $row->status,
                        'reason' => $row->reason,
                        'changed_at' => $row->created_at,
                    ];
                }
            }
        }

        $results = [];
        foreach ($lifts as $lift) {
            $id = (int) $lift->id;
            $status = $latestStatus[$id] ?? null;
            $results[] = [
                'id' => $id,
                'name' => $lift->name,
                'lift_type' => $lift->lift_type,
                'status' => $status['status'] ?? 'unknown',
                'reason' => $status['reason'] ?? null,
                'changed_at' => $status['changed_at'] ?? null,
            ];
        }

        return $results;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function weatherWarnings(string $conn): array
    {
        if (! Schema::connection($conn)->hasTable('weather_warning')) {
            return [];
        }

        return WeatherWarning::on($conn)
            ->where('status', WeatherWarning::STATUS_ACTIVE)
            ->orderByDesc('raised_at')
            ->limit(50)
            ->get()
            ->map(fn (WeatherWarning $w) => [
                'id' => (int) $w->getKey(),
                'variable' => $w->getAttribute('variable'),
                'threshold' => (float) $w->getAttribute('threshold'),
                'forecast_value' => (float) $w->getAttribute('forecast_value'),
                'status' => $w->getAttribute('status'),
                'starts_at' => WeatherDate::iso($w->getAttribute('starts_at')),
                'ends_at' => WeatherDate::iso($w->getAttribute('ends_at')),
                'raised_at' => WeatherDate::iso($w->getAttribute('raised_at')),
            ])
            ->all();
    }

    private function currency(string $conn): string
    {
        if (! Schema::connection($conn)->hasTable('core_tenant_settings')) {
            return 'EUR';
        }

        $settings = TenantSettings::on($conn)->query()->first();

        return $settings?->getAttribute('currency') ?: 'EUR';
    }
}
