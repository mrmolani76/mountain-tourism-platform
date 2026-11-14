<?php

namespace Modules\Reports\App\Services;

use Illuminate\Support\Facades\Schema;
use Modules\Core\App\Models\TenantSettings;
use Modules\Weather\App\Models\WeatherCancellation;
use Modules\Weather\App\Support\WeatherDate;

/**
 * Weather cancellation list for a date range (cancelled_at date).
 */
class WeatherCancellationReportService
{
    /**
     * @return array{
     *   currency: string,
     *   from_date: string,
     *   to_date: string,
     *   basis: string,
     *   total_cents: int,
     *   rows: list<array<string, mixed>>,
     *   count: int
     * }
     */
    public function list(string $fromDate, string $toDate): array
    {
        $conn = config('tenancy.connection_name');
        $rows = [];

        if (Schema::connection($conn)->hasTable('weather_cancellation')) {
            $models = WeatherCancellation::on($conn)
                ->whereDate('cancelled_at', '>=', $fromDate)
                ->whereDate('cancelled_at', '<=', $toDate)
                ->orderByDesc('cancelled_at')
                ->get();

            foreach ($models as $model) {
                $rows[] = [
                    'key' => (string) $model->getKey(),
                    'label' => sprintf(
                        '%s #%d',
                        (string) $model->getAttribute('target_type'),
                        (int) $model->getAttribute('target_id')
                    ),
                    'amount_cents' => 0,
                    'id' => (int) $model->getKey(),
                    'warning_id' => $model->getAttribute('warning_id') === null
                        ? null
                        : (int) $model->getAttribute('warning_id'),
                    'target_type' => $model->getAttribute('target_type'),
                    'target_id' => (int) $model->getAttribute('target_id'),
                    'cancelled_at' => WeatherDate::iso($model->getAttribute('cancelled_at')),
                    'cancelled_by' => $model->getAttribute('cancelled_by') === null
                        ? null
                        : (int) $model->getAttribute('cancelled_by'),
                    'reason' => $model->getAttribute('reason'),
                    'notes' => $model->getAttribute('notes'),
                ];
            }
        }

        return [
            'currency' => $this->currency(),
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'basis' => 'weather_cancellations',
            'total_cents' => 0,
            'count' => count($rows),
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
