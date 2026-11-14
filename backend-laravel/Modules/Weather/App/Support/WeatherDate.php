<?php

namespace Modules\Weather\App\Support;

use DateTimeInterface;

final class WeatherDate
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
