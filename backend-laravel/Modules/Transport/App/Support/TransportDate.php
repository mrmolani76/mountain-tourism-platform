<?php

namespace Modules\Transport\App\Support;

use DateTimeInterface;

final class TransportDate
{
    public static function iso(?DateTimeInterface $date): ?string
    {
        return $date?->format('Y-m-d\TH:i:s.u\Z');
    }

    public static function date(?DateTimeInterface $date): ?string
    {
        return $date?->format('Y-m-d');
    }

    public static function time(?DateTimeInterface $date): ?string
    {
        return $date?->format('H:i:s');
    }
}
