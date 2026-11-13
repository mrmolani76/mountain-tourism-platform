<?php

namespace Modules\Pos\App\Models;

use App\Models\BaseModel;

class OutletProduct extends BaseModel
{
    public const UPDATED_AT = null;

    protected $connection = 'tenant';

    protected $table = 'pos_outlet_product';

    protected $fillable = ['outlet_id', 'product_id', 'is_available', 'sort_order'];

    protected $casts = [
        'outlet_id' => 'integer',
        'product_id' => 'integer',
        'is_available' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'outlet_id' => (int) $this->getAttribute('outlet_id'),
            'product_id' => (int) $this->getAttribute('product_id'),
            'is_available' => (bool) $this->getAttribute('is_available'),
            'sort_order' => (int) $this->getAttribute('sort_order'),
        ];
    }
}
