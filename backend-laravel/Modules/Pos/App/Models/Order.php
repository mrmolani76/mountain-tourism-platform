<?php

namespace Modules\Pos\App\Models;

use App\Models\BaseModel;
use Modules\Pos\App\Support\PosDate;

class Order extends BaseModel
{
    public const STATUS_OPEN = 'open';

    public const STATUS_SENT = 'sent';

    public const STATUS_BILLED = 'billed';

    public const STATUS_PAID = 'paid';

    public const STATUS_VOID = 'void';

    public const METHOD_CASH = 'cash';

    public const METHOD_CARD = 'card';

    public const METHOD_ROOM = 'room';

    public const METHODS = [self::METHOD_CASH, self::METHOD_CARD, self::METHOD_ROOM];

    protected $connection = 'tenant';

    protected $table = 'pos_order';

    protected $fillable = [
        'outlet_id', 'shift_id', 'stay_id', 'folio_id', 'table_label', 'status',
        'subtotal_cents', 'tax_cents', 'total_cents', 'charge_id', 'payment_method',
        'opened_by', 'opened_at', 'closed_at',
    ];

    protected $casts = [
        'outlet_id' => 'integer',
        'shift_id' => 'integer',
        'stay_id' => 'integer',
        'folio_id' => 'integer',
        'subtotal_cents' => 'integer',
        'tax_cents' => 'integer',
        'total_cents' => 'integer',
        'charge_id' => 'integer',
        'opened_by' => 'integer',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function shift()
    {
        return $this->belongsTo(CashierShift::class, 'shift_id');
    }

    public function isOpen(): bool
    {
        return in_array($this->getAttribute('status'), [self::STATUS_OPEN, self::STATUS_SENT], true);
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'outlet_id' => (int) $this->getAttribute('outlet_id'),
            'shift_id' => $this->getAttribute('shift_id') === null
                ? null
                : (int) $this->getAttribute('shift_id'),
            'stay_id' => $this->getAttribute('stay_id') === null
                ? null
                : (int) $this->getAttribute('stay_id'),
            'folio_id' => $this->getAttribute('folio_id') === null
                ? null
                : (int) $this->getAttribute('folio_id'),
            'table_label' => $this->getAttribute('table_label'),
            'status' => $this->getAttribute('status'),
            'subtotal_cents' => (int) $this->getAttribute('subtotal_cents'),
            'tax_cents' => (int) $this->getAttribute('tax_cents'),
            'total_cents' => (int) $this->getAttribute('total_cents'),
            'charge_id' => $this->getAttribute('charge_id') === null
                ? null
                : (int) $this->getAttribute('charge_id'),
            'payment_method' => $this->getAttribute('payment_method'),
            'opened_by' => $this->getAttribute('opened_by') === null
                ? null
                : (int) $this->getAttribute('opened_by'),
            'opened_at' => PosDate::iso($this->getAttribute('opened_at')),
            'closed_at' => PosDate::iso($this->getAttribute('closed_at')),
            'created_at' => PosDate::iso($this->getAttribute('created_at')),
            'updated_at' => PosDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
