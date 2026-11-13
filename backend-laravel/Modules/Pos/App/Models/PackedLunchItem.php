<?php

namespace Modules\Pos\App\Models;

use App\Models\BaseModel;

class PackedLunchItem extends BaseModel
{
    public $timestamps = false;

    protected $connection = 'tenant';

    protected $table = 'pos_packed_lunch_item';

    protected $fillable = ['packed_lunch_id', 'product_id', 'description', 'quantity'];

    protected $casts = [
        'packed_lunch_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'integer',
    ];

    public function packedLunch()
    {
        return $this->belongsTo(PackedLunch::class, 'packed_lunch_id');
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'packed_lunch_id' => (int) $this->getAttribute('packed_lunch_id'),
            'product_id' => $this->getAttribute('product_id') === null
                ? null
                : (int) $this->getAttribute('product_id'),
            'description' => $this->getAttribute('description'),
            'quantity' => (int) $this->getAttribute('quantity'),
        ];
    }
}
