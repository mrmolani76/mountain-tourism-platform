<?php

namespace Modules\Folio\App\Models;

use App\Models\BaseModel;
use Modules\Folio\App\Support\FolioDate;

/** Audit row for a voided charge or payment: reason + actor + timestamp. */
class FolioVoid extends BaseModel
{
    public const REASON_WEATHER = 'weather';

    public const UPDATED_AT = null;

    protected $connection = 'tenant';

    protected $table = 'folio_void';

    protected $fillable = [
        'folio_id', 'charge_id', 'payment_id', 'reason', 'reason_code', 'voided_by',
    ];

    protected $casts = [
        'folio_id' => 'integer',
        'charge_id' => 'integer',
        'payment_id' => 'integer',
        'voided_by' => 'integer',
        'created_at' => 'datetime',
    ];

    public function charge()
    {
        return $this->belongsTo(Charge::class, 'charge_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'folio_id' => (int) $this->getAttribute('folio_id'),
            'charge_id' => $this->getAttribute('charge_id') === null
                ? null
                : (int) $this->getAttribute('charge_id'),
            'payment_id' => $this->getAttribute('payment_id') === null
                ? null
                : (int) $this->getAttribute('payment_id'),
            'reason' => $this->getAttribute('reason'),
            'reason_code' => $this->getAttribute('reason_code'),
            'voided_by' => $this->getAttribute('voided_by') === null
                ? null
                : (int) $this->getAttribute('voided_by'),
            'created_at' => FolioDate::iso($this->getAttribute('created_at')),
        ];
    }
}
