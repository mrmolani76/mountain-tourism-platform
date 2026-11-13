<?php

namespace Modules\Rental\App\Models;

use App\Models\BaseModel;
use Modules\Rental\App\Support\RentalDate;

class EquipmentItem extends BaseModel
{
    public const CONDITION_OK = 'ok';

    public const CONDITION_DAMAGED = 'damaged';

    public const CONDITION_RETIRED = 'retired';

    public const CONDITIONS = [
        self::CONDITION_OK,
        self::CONDITION_DAMAGED,
        self::CONDITION_RETIRED,
    ];

    protected $connection = 'tenant';

    protected $table = 'rental_equipment_item';

    protected $fillable = [
        'equipment_type_id', 'item_code', 'size', 'condition', 'is_active',
    ];

    protected $casts = [
        'equipment_type_id' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function equipmentType()
    {
        return $this->belongsTo(EquipmentType::class, 'equipment_type_id');
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'equipment_type_id' => (int) $this->getAttribute('equipment_type_id'),
            'item_code' => $this->getAttribute('item_code'),
            'size' => $this->getAttribute('size'),
            'condition' => $this->getAttribute('condition'),
            'is_active' => (bool) $this->getAttribute('is_active'),
            'created_at' => RentalDate::iso($this->getAttribute('created_at')),
            'updated_at' => RentalDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
