<?php

namespace Modules\Reports\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Modules\Reports\App\Data\ReportRangeData;
use Modules\Reports\App\Services\CsvExportService;
use Modules\Reports\App\Services\RevenueReportService;
use Modules\Reports\App\Services\WeatherCancellationReportService;
use Modules\Reports\App\Support\ReportsAuthz;

class ReportController extends Controller
{
    public function __construct(
        private readonly RevenueReportService $revenue,
        private readonly WeatherCancellationReportService $weatherCancellations,
        private readonly CsvExportService $csv,
    ) {
    }

    public function revenueByOutlet(ReportRangeData $request): JsonResponse
    {
        if ($denied = ReportsAuthz::denyUnlessOwnerOrManager()) {
            return $denied;
        }

        return response()->json(
            $this->revenue->byOutlet($request->from_date, $request->to_date),
            200
        );
    }

    public function revenueByService(ReportRangeData $request): JsonResponse
    {
        if ($denied = ReportsAuthz::denyUnlessOwnerOrManager()) {
            return $denied;
        }

        return response()->json(
            $this->revenue->byService($request->from_date, $request->to_date),
            200
        );
    }

    public function revenueByPaymentMethod(ReportRangeData $request): JsonResponse
    {
        if ($denied = ReportsAuthz::denyUnlessOwnerOrManager()) {
            return $denied;
        }

        return response()->json(
            $this->revenue->byPaymentMethod($request->from_date, $request->to_date),
            200
        );
    }

    public function weatherCancellations(ReportRangeData $request): JsonResponse
    {
        if ($denied = ReportsAuthz::denyUnlessOwnerOrManager()) {
            return $denied;
        }

        return response()->json(
            $this->weatherCancellations->list($request->from_date, $request->to_date),
            200
        );
    }

    public function revenueByOutletCsv(ReportRangeData $request): Response
    {
        if ($denied = ReportsAuthz::denyUnlessOwnerOrManager()) {
            return $this->csvDenied($denied);
        }

        $data = $this->revenue->byOutlet($request->from_date, $request->to_date);

        return $this->csvAttachment(
            'revenue-by-outlet',
            $request->from_date,
            $request->to_date,
            $this->revenueRowsToCsv($data)
        );
    }

    public function revenueByServiceCsv(ReportRangeData $request): Response
    {
        if ($denied = ReportsAuthz::denyUnlessOwnerOrManager()) {
            return $this->csvDenied($denied);
        }

        $data = $this->revenue->byService($request->from_date, $request->to_date);

        return $this->csvAttachment(
            'revenue-by-service',
            $request->from_date,
            $request->to_date,
            $this->revenueRowsToCsv($data)
        );
    }

    public function revenueByPaymentMethodCsv(ReportRangeData $request): Response
    {
        if ($denied = ReportsAuthz::denyUnlessOwnerOrManager()) {
            return $this->csvDenied($denied);
        }

        $data = $this->revenue->byPaymentMethod($request->from_date, $request->to_date);

        return $this->csvAttachment(
            'revenue-by-payment-method',
            $request->from_date,
            $request->to_date,
            $this->revenueRowsToCsv($data)
        );
    }

    public function weatherCancellationsCsv(ReportRangeData $request): Response
    {
        if ($denied = ReportsAuthz::denyUnlessOwnerOrManager()) {
            return $this->csvDenied($denied);
        }

        $data = $this->weatherCancellations->list($request->from_date, $request->to_date);
        $bodyRows = [];
        foreach ($data['rows'] as $row) {
            $bodyRows[] = [
                (string) ($row['id'] ?? ''),
                (string) ($row['target_type'] ?? ''),
                (string) ($row['target_id'] ?? ''),
                (string) ($row['cancelled_at'] ?? ''),
                (string) ($row['reason'] ?? ''),
                (string) ($row['notes'] ?? ''),
            ];
        }

        $csv = $this->csv->build(
            ['id', 'target_type', 'target_id', 'cancelled_at', 'reason', 'notes'],
            $bodyRows
        );

        return $this->csvAttachment(
            'weather-cancellations',
            $request->from_date,
            $request->to_date,
            $csv
        );
    }

    /**
     * @param  array{rows: list<array{key: string, label: string, amount_cents: int}>, total_cents: int}  $data
     */
    private function revenueRowsToCsv(array $data): string
    {
        $bodyRows = [];
        foreach ($data['rows'] as $row) {
            $bodyRows[] = [
                (string) $row['key'],
                (string) $row['label'],
                (string) $row['amount_cents'],
            ];
        }
        $bodyRows[] = ['total', 'Total', (string) $data['total_cents']];

        return $this->csv->build(['key', 'label', 'amount_cents'], $bodyRows);
    }

    private function csvAttachment(string $slug, string $from, string $to, string $csv): Response
    {
        $filename = sprintf('%s_%s_%s.csv', $slug, $from, $to);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function csvDenied(JsonResponse $denied): Response
    {
        return response($denied->getContent() ?: '', $denied->getStatusCode(), [
            'Content-Type' => 'application/json',
        ]);
    }
}
