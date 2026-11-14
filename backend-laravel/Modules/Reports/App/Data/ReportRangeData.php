<?php

namespace Modules\Reports\App\Data;

/**
 * Query payload for date-range reports (EQMS-style Spatie Data).
 */
class ReportRangeData extends ReportsData
{
    public function __construct(
        public string $from_date,
        public string $to_date,
    ) {}

    public static function rules(): array
    {
        return [
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
        ];
    }
}
