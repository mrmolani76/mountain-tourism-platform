<?php

namespace Modules\Folio\App\Support;

use DateTimeInterface;

/** DRF date parity for folio payloads (no dependency on the Hotel module). */
final class FolioDate
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
