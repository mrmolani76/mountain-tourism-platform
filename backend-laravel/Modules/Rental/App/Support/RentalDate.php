<?php

namespace Modules\Rental\App\Support;

use DateTimeInterface;

final class RentalDate
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
