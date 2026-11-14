<?php

namespace Modules\Reports\App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Core\App\Models\Outlet;
use Modules\Core\App\Models\TenantSettings;
use Modules\Folio\App\Models\Charge;
use Modules\Folio\App\Models\Payment;

/**
 * Date-range revenue and payment reports. Integer cents; non-voided rows only.
 */
class RevenueReportService
{
    /**
     * @return array{
     *   currency: string,
     *   from_date: string,
     *   to_date: string,
     *   basis: string,
     *   total_cents: int,
     *   rows: list<array{key: string, label: string, amount_cents: int}>
     * }
     */
    public function byOutlet(string $fromDate, string $toDate): array
    {
        $conn = config('tenancy.connection_name');
        $rows = [];
        $total = 0;

        if (Schema::connection($conn)->hasTable('folio_charge')) {
            $grouped = Charge::on($conn)
                ->where('is_voided', false)
                ->whereDate('posted_on', '>=', $fromDate)
                ->whereDate('posted_on', '<=', $toDate)
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
                $key = $outletId === null ? 'none' : (string) $outletId;
                $label = $outletId === null
                    ? 'No outlet'
                    : (string) ($outletNames[$outletId] ?? ('Outlet #'.$outletId));
                $rows[] = [
                    'key' => $key,
                    'label' => $label,
                    'amount_cents' => $amount,
                ];
            }
        }

        return $this->envelope($fromDate, $toDate, 'non_voided_charges', $total, $rows);
    }

    /**
     * Group by source_type (all charge sources as rows).
     *
     * @return array{
     *   currency: string,
     *   from_date: string,
     *   to_date: string,
     *   basis: string,
     *   total_cents: int,
     *   rows: list<array{key: string, label: string, amount_cents: int}>
     * }
     */
    public function byService(string $fromDate, string $toDate): array
    {
        $conn = config('tenancy.connection_name');
        $rows = [];
        $total = 0;

        if (Schema::connection($conn)->hasTable('folio_charge')) {
            $grouped = Charge::on($conn)
                ->where('is_voided', false)
                ->whereDate('posted_on', '>=', $fromDate)
                ->whereDate('posted_on', '<=', $toDate)
                ->select('source_type', DB::raw('SUM(amount_cents) as amount_cents'))
                ->groupBy('source_type')
                ->get();

            foreach ($grouped as $row) {
                $source = $row->source_type === null || $row->source_type === ''
                    ? 'unknown'
                    : (string) $row->source_type;
                $amount = (int) $row->amount_cents;
                $total += $amount;
                $rows[] = [
                    'key' => $source,
                    'label' => $source,
                    'amount_cents' => $amount,
                ];
            }
        }

        return $this->envelope($fromDate, $toDate, 'non_voided_charges', $total, $rows);
    }

    /**
     * Group payments by method; date from created_at.
     *
     * @return array{
     *   currency: string,
     *   from_date: string,
     *   to_date: string,
     *   basis: string,
     *   total_cents: int,
     *   rows: list<array{key: string, label: string, amount_cents: int}>
     * }
     */
    public function byPaymentMethod(string $fromDate, string $toDate): array
    {
        $conn = config('tenancy.connection_name');
        $rows = [];
        $total = 0;

        if (Schema::connection($conn)->hasTable('folio_payment')) {
            $grouped = Payment::on($conn)
                ->where('is_voided', false)
                ->whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->select('method', DB::raw('SUM(amount_cents) as amount_cents'))
                ->groupBy('method')
                ->get();

            foreach ($grouped as $row) {
                $method = $row->method === null || $row->method === ''
                    ? 'unknown'
                    : (string) $row->method;
                $amount = (int) $row->amount_cents;
                $total += $amount;
                $rows[] = [
                    'key' => $method,
                    'label' => $method,
                    'amount_cents' => $amount,
                ];
            }
        }

        return $this->envelope($fromDate, $toDate, 'non_voided_payments', $total, $rows);
    }

    /**
     * @param  list<array{key: string, label: string, amount_cents: int}>  $rows
     * @return array{
     *   currency: string,
     *   from_date: string,
     *   to_date: string,
     *   basis: string,
     *   total_cents: int,
     *   rows: list<array{key: string, label: string, amount_cents: int}>
     * }
     */
    private function envelope(string $fromDate, string $toDate, string $basis, int $total, array $rows): array
    {
        return [
            'currency' => $this->currency(),
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'basis' => $basis,
            'total_cents' => $total,
            'rows' => $rows,
        ];
    }

    private function currency(): string
    {
        $conn = config('tenancy.connection_name');
        if (! Schema::connection($conn)->hasTable('core_tenant_settings')) {
            return 'EUR';
        }

        $settings = TenantSettings::on($conn)->query()->first();

        return $settings?->getAttribute('currency') ?: 'EUR';
    }
}
