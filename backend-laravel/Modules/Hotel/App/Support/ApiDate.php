<?php

namespace Modules\Hotel\App\Support;

use DateTimeInterface;

/** DRF `Y-m-d\TH:i:s.u\Z` parity for tenant API payloads. */
final class ApiDate
{
    public static function iso(?DateTimeInterface $date): ?string
    {
        return $date?->format('Y-m-d\TH:i:s.u\Z');
    }

    public static function date(?DateTimeInterface $date): ?string
    {
        return $date?->format('Y-m-d');
    }
}
