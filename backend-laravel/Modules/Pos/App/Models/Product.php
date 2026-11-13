<?php

namespace Modules\Pos\App\Models;

use App\Models\BaseModel;
use Modules\Pos\App\Support\PosDate;

class Product extends BaseModel
{
    protected $connection = 'tenant';

    protected $table = 'pos_product';

    protected $fillable = [
        'category_id', 'code', 'name', 'description',
        'price_cents', 'tax_rate_bp', 'is_active',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'price_cents' => 'integer',
        'tax_rate_bp' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function outletProducts()
    {
        return $this->hasMany(OutletProduct::class, 'product_id');
    }

    public function seasonRows()
    {
        return $this->hasMany(ProductSeason::class, 'product_id');
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'category_id' => $this->getAttribute('category_id') === null
                ? null
                : (int) $this->getAttribute('category_id'),
            'code' => $this->getAttribute('code'),
            'name' => $this->getAttribute('name'),
            'description' => $this->getAttribute('description'),
            'price_cents' => (int) $this->getAttribute('price_cents'),
            'tax_rate_bp' => (int) $this->getAttribute('tax_rate_bp'),
            'is_active' => (bool) $this->getAttribute('is_active'),
            'created_at' => PosDate::iso($this->getAttribute('created_at')),
            'updated_at' => PosDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
