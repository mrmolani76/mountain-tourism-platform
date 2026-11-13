<?php

namespace Modules\Rental\App\Models;

use App\Models\BaseModel;
use Modules\Rental\App\Support\RentalDate;

class EquipmentType extends BaseModel
{
    protected $connection = 'tenant';

    protected $table = 'rental_equipment_type';

    protected $fillable = ['name', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(EquipmentItem::class, 'equipment_type_id');
    }

    public function seasonPrices()
    {
        return $this->hasMany(SeasonPrice::class, 'equipment_type_id');
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'name' => $this->getAttribute('name'),
            'description' => $this->getAttribute('description'),
            'is_active' => (bool) $this->getAttribute('is_active'),
            'created_at' => RentalDate::iso($this->getAttribute('created_at')),
            'updated_at' => RentalDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
