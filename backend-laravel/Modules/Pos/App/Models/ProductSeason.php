<?php

namespace Modules\Pos\App\Models;

use App\Models\BaseModel;

class ProductSeason extends BaseModel
{
    public const UPDATED_AT = null;

    protected $connection = 'tenant';

    protected $table = 'pos_product_season';

    protected $fillable = ['product_id', 'season_id', 'is_available', 'price_cents'];

    protected $casts = [
        'product_id' => 'integer',
        'season_id' => 'integer',
        'is_available' => 'boolean',
        'price_cents' => 'integer',
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
            'product_id' => (int) $this->getAttribute('product_id'),
            'season_id' => (int) $this->getAttribute('season_id'),
            'is_available' => (bool) $this->getAttribute('is_available'),
            'price_cents' => $this->getAttribute('price_cents') === null
                ? null
                : (int) $this->getAttribute('price_cents'),
        ];
    }
}
