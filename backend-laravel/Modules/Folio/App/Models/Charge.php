<?php

namespace Modules\Folio\App\Models;

use App\Models\BaseModel;
use DomainException;
use Modules\Folio\App\Support\FolioDate;

/**
 * Append-only charge line. Amounts are integer minor units.
 * Never update amount/outlet/source; void instead (reason + actor + timestamp).
 */
class Charge extends BaseModel
{
    public const SOURCE_ROOM_NIGHT = 'room_night';

    public const SOURCE_BOARD = 'board';

    public const SOURCE_ORDER = 'order';

    public const SOURCE_BOOKING = 'booking';

    public const SOURCE_RENTAL = 'rental';

    public const SOURCE_SERVICE = 'service';

    public const SOURCE_TRANSPORT = 'transport';

    public const SOURCE_OTHER = 'other';

    public const SOURCES = [
        self::SOURCE_ROOM_NIGHT,
        self::SOURCE_BOARD,
        self::SOURCE_ORDER,
        self::SOURCE_BOOKING,
        self::SOURCE_RENTAL,
        self::SOURCE_SERVICE,
        self::SOURCE_TRANSPORT,
        self::SOURCE_OTHER,
    ];

    public const UPDATED_AT = null;

    protected $connection = 'tenant';

    protected $table = 'folio_charge';

    protected $fillable = [
        'folio_id', 'outlet_id', 'posted_on', 'description', 'amount_cents',
        'tax_cents', 'source_type', 'source_id', 'stay_id', 'is_voided', 'created_by',
    ];

    protected $casts = [
        'folio_id' => 'integer',
        'outlet_id' => 'integer',
        'posted_on' => 'date',
        'amount_cents' => 'integer',
        'tax_cents' => 'integer',
        'stay_id' => 'integer',
        'is_voided' => 'boolean',
        'created_by' => 'integer',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function (self $charge) {
            if (array_keys($charge->getDirty()) !== ['is_voided']) {
                throw new DomainException('Charge rows are append-only. Void the charge instead.');
            }
        });

        static::deleting(function () {
            throw new DomainException('Charge rows cannot be deleted. Void the charge instead.');
        });
    }

    public function folio()
    {
        return $this->belongsTo(Folio::class, 'folio_id');
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'folio_id' => (int) $this->getAttribute('folio_id'),
            'outlet_id' => $this->getAttribute('outlet_id') === null
                ? null
                : (int) $this->getAttribute('outlet_id'),
            'posted_on' => FolioDate::date($this->getAttribute('posted_on')),
            'description' => $this->getAttribute('description'),
            'amount_cents' => (int) $this->getAttribute('amount_cents'),
            'tax_cents' => (int) $this->getAttribute('tax_cents'),
            'source_type' => $this->getAttribute('source_type'),
            'source_id' => $this->getAttribute('source_id'),
            'stay_id' => $this->getAttribute('stay_id') === null
                ? null
                : (int) $this->getAttribute('stay_id'),
            'is_voided' => (bool) $this->getAttribute('is_voided'),
            'created_by' => $this->getAttribute('created_by') === null
                ? null
                : (int) $this->getAttribute('created_by'),
            'created_at' => FolioDate::iso($this->getAttribute('created_at')),
        ];
    }
}
