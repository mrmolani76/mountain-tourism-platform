<?php

namespace Modules\Pos\App\Models;

use App\Models\BaseModel;
use Modules\Pos\App\Support\PosDate;

class Category extends BaseModel
{
    protected $connection = 'tenant';

    protected $table = 'pos_category';

    protected $fillable = ['name', 'sort_order', 'is_active'];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'name' => $this->getAttribute('name'),
            'sort_order' => (int) $this->getAttribute('sort_order'),
            'is_active' => (bool) $this->getAttribute('is_active'),
            'created_at' => PosDate::iso($this->getAttribute('created_at')),
            'updated_at' => PosDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
