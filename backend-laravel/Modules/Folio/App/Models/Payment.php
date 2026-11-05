<?php

namespace Modules\Folio\App\Models;

use App\Models\BaseModel;
use DomainException;
use Modules\Folio\App\Support\FolioDate;

/** Append-only payment row. Split tender = several rows. */
class Payment extends BaseModel
{
    public const METHOD_CASH = 'cash';

    public const METHOD_CARD = 'card';

    public const METHODS = [self::METHOD_CASH, self::METHOD_CARD];

    public const UPDATED_AT = null;

    protected $connection = 'tenant';

    protected $table = 'folio_payment';

    protected $fillable = [
        'folio_id', 'method', 'amount_cents', 'reference', 'is_voided', 'created_by',
    ];

    protected $casts = [
        'folio_id' => 'integer',
        'amount_cents' => 'integer',
        'is_voided' => 'boolean',
        'created_by' => 'integer',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function (self $payment) {
            if (array_keys($payment->getDirty()) !== ['is_voided']) {
                throw new DomainException('Payment rows are append-only. Void the payment instead.');
            }
        });

        static::deleting(function () {
            throw new DomainException('Payment rows cannot be deleted. Void the payment instead.');
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
            'method' => $this->getAttribute('method'),
            'amount_cents' => (int) $this->getAttribute('amount_cents'),
            'reference' => $this->getAttribute('reference'),
            'is_voided' => (bool) $this->getAttribute('is_voided'),
            'created_by' => $this->getAttribute('created_by') === null
                ? null
                : (int) $this->getAttribute('created_by'),
            'created_at' => FolioDate::iso($this->getAttribute('created_at')),
        ];
    }
}
