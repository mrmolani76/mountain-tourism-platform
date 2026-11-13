<?php

namespace Modules\Pos\App\Models;

use App\Models\BaseModel;
use Modules\Pos\App\Support\PosDate;

class CashierShift extends BaseModel
{
    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    protected $connection = 'tenant';

    protected $table = 'pos_cashier_shift';

    protected $fillable = [
        'outlet_id', 'opened_by', 'opened_at', 'closed_by', 'closed_at',
        'opening_float_cents', 'expected_cash_cents', 'counted_cash_cents',
        'difference_cents', 'status', 'notes',
    ];

    protected $casts = [
        'outlet_id' => 'integer',
        'opened_by' => 'integer',
        'closed_by' => 'integer',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_float_cents' => 'integer',
        'expected_cash_cents' => 'integer',
        'counted_cash_cents' => 'integer',
        'difference_cents' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function isOpen(): bool
    {
        return $this->getAttribute('status') === self::STATUS_OPEN;
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'outlet_id' => (int) $this->getAttribute('outlet_id'),
            'opened_by' => (int) $this->getAttribute('opened_by'),
            'opened_at' => PosDate::iso($this->getAttribute('opened_at')),
            'closed_by' => $this->getAttribute('closed_by') === null
                ? null
                : (int) $this->getAttribute('closed_by'),
            'closed_at' => PosDate::iso($this->getAttribute('closed_at')),
            'opening_float_cents' => (int) $this->getAttribute('opening_float_cents'),
            'expected_cash_cents' => (int) $this->getAttribute('expected_cash_cents'),
            'counted_cash_cents' => $this->getAttribute('counted_cash_cents') === null
                ? null
                : (int) $this->getAttribute('counted_cash_cents'),
            'difference_cents' => $this->getAttribute('difference_cents') === null
                ? null
                : (int) $this->getAttribute('difference_cents'),
            'status' => $this->getAttribute('status'),
            'notes' => $this->getAttribute('notes'),
            'created_at' => PosDate::iso($this->getAttribute('created_at')),
            'updated_at' => PosDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
