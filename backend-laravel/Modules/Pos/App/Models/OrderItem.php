<?php

namespace Modules\Pos\App\Models;

use App\Models\BaseModel;
use Modules\Pos\App\Support\PosDate;

class OrderItem extends BaseModel
{
    public const UPDATED_AT = null;

    protected $connection = 'tenant';

    protected $table = 'pos_order_item';

    protected $fillable = [
        'order_id', 'product_id', 'description', 'quantity', 'unit_price_cents',
        'tax_rate_bp', 'tax_cents', 'line_total_cents', 'note', 'is_board_included',
    ];

    protected $casts = [
        'order_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'integer',
        'unit_price_cents' => 'integer',
        'tax_rate_bp' => 'integer',
        'tax_cents' => 'integer',
        'line_total_cents' => 'integer',
        'is_board_included' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'order_id' => (int) $this->getAttribute('order_id'),
            'product_id' => $this->getAttribute('product_id') === null
                ? null
                : (int) $this->getAttribute('product_id'),
            'description' => $this->getAttribute('description'),
            'quantity' => (int) $this->getAttribute('quantity'),
            'unit_price_cents' => (int) $this->getAttribute('unit_price_cents'),
            'tax_rate_bp' => (int) $this->getAttribute('tax_rate_bp'),
            'tax_cents' => (int) $this->getAttribute('tax_cents'),
            'line_total_cents' => (int) $this->getAttribute('line_total_cents'),
            'note' => $this->getAttribute('note'),
            'is_board_included' => (bool) $this->getAttribute('is_board_included'),
            'created_at' => PosDate::iso($this->getAttribute('created_at')),
        ];
    }
}
