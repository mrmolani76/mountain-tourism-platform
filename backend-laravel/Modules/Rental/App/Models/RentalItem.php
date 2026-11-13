<?php

namespace Modules\Rental\App\Models;

use App\Models\BaseModel;
use Modules\Rental\App\Support\RentalDate;

class RentalItem extends BaseModel
{
    public const STATUS_OUT = 'out';

    public const STATUS_RETURNED = 'returned';

    public const STATUS_DAMAGED = 'damaged';

    protected $connection = 'tenant';

    protected $table = 'rental_rental_item';

    protected $fillable = [
        'rental_id', 'equipment_item_id', 'day_rate_cents', 'days', 'line_total_cents',
        'status', 'returned_at', 'condition_on_return', 'late_days',
        'late_fee_cents', 'damage_fee_cents',
    ];

    protected $casts = [
        'rental_id' => 'integer',
        'equipment_item_id' => 'integer',
        'day_rate_cents' => 'integer',
        'days' => 'integer',
        'line_total_cents' => 'integer',
        'returned_at' => 'datetime',
        'late_days' => 'integer',
        'late_fee_cents' => 'integer',
        'damage_fee_cents' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function rental()
    {
        return $this->belongsTo(Rental::class, 'rental_id');
    }

    public function equipmentItem()
    {
        return $this->belongsTo(EquipmentItem::class, 'equipment_item_id');
    }

    public function isOut(): bool
    {
        return $this->getAttribute('status') === self::STATUS_OUT;
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'rental_id' => (int) $this->getAttribute('rental_id'),
            'equipment_item_id' => (int) $this->getAttribute('equipment_item_id'),
            'day_rate_cents' => (int) $this->getAttribute('day_rate_cents'),
            'days' => (int) $this->getAttribute('days'),
            'line_total_cents' => (int) $this->getAttribute('line_total_cents'),
            'status' => $this->getAttribute('status'),
            'returned_at' => RentalDate::iso($this->getAttribute('returned_at')),
            'condition_on_return' => $this->getAttribute('condition_on_return'),
            'late_days' => (int) $this->getAttribute('late_days'),
            'late_fee_cents' => (int) $this->getAttribute('late_fee_cents'),
            'damage_fee_cents' => (int) $this->getAttribute('damage_fee_cents'),
            'created_at' => RentalDate::iso($this->getAttribute('created_at')),
            'updated_at' => RentalDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
