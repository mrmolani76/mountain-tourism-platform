<?php

namespace App\Services;

use Carbon\Carbon;
use DateTimeZone;

/**
 * eQMS complere/app/Services/DateTimeZoneMutator.php — Mountain subset (no TenantSetting cache yet).
 */
class DateTimeZoneMutator
{
    public static function mutate($timestamp, $format = null, $timezone = null, bool $includeTime = false): ?string
    {
        if (! $timestamp) {
            return null;
        }

        $format = $format ?? setDateFormat();
        $timezone = $timezone ?? config('app.timezone');

        if ($includeTime && ! str_contains($format, 'H')) {
            $format .= ' H:i';
        }

        return Carbon::parse($timestamp)->timezone($timezone)->format($format);
    }

    public static function tenantTimezone(): string
    {
        $zone = (string) config('app.timezone', 'UTC');

        try {
            return (new DateTimeZone($zone))->getName();
        }
        catch (\Exception) {
            return 'UTC';
        }
    }

    public static function convertToBaseDate($date, $userTz = null, $format = null, $baseTz = 'UTC'): string
    {
        $userTz = $userTz ?? config('app.timezone');

        return Carbon::parse($date, $userTz)->timezone($baseTz)->format('Y-m-d H:i:s');
    }

    /** Calendar DATE columns — no timezone shift. */
    public static function mutateDate($date, $format = null): ?string
    {
        if (! $date) {
            return null;
        }

        return Carbon::parse($date)->format($format ?? setDateFormat());
    }

    public static function toBaseCalendarDate($date): ?Carbon
    {
        if (! $date) {
            return null;
        }

        return Carbon::parse($date)->startOfDay();
    }
}
