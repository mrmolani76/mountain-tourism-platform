<?php

namespace Modules\Rental\App\Models;

use App\Models\BaseModel;
use Modules\Rental\App\Support\RentalDate;

class Rental extends BaseModel
{
    public const CUSTOMER_ROOM = 'room';

    public const CUSTOMER_WALK_IN = 'walk_in';

    public const STATUS_OPEN = 'open';

    public const STATUS_RETURNED = 'returned';

    public const STATUS_LATE = 'late';

    public const STATUS_VOID = 'void';

    protected $connection = 'tenant';

    protected $table = 'rental_rental';

    protected $fillable = [
        'reference', 'customer_type', 'stay_id', 'folio_id', 'guest_name',
        'rented_by', 'rented_at', 'due_on', 'status', 'deposit_cents',
        'charge_id', 'notes',
    ];

    protected $casts = [
        'stay_id' => 'integer',
        'folio_id' => 'integer',
        'rented_by' => 'integer',
        'rented_at' => 'datetime',
        'due_on' => 'date',
        'deposit_cents' => 'integer',
        'charge_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(RentalItem::class, 'rental_id');
    }

    public function isOpen(): bool
    {
        return in_array($this->getAttribute('status'), [self::STATUS_OPEN, self::STATUS_LATE], true);
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'reference' => $this->getAttribute('reference'),
            'customer_type' => $this->getAttribute('customer_type'),
            'stay_id' => $this->getAttribute('stay_id') === null
                ? null
                : (int) $this->getAttribute('stay_id'),
            'folio_id' => $this->getAttribute('folio_id') === null
                ? null
                : (int) $this->getAttribute('folio_id'),
            'guest_name' => $this->getAttribute('guest_name'),
            'rented_by' => $this->getAttribute('rented_by') === null
                ? null
                : (int) $this->getAttribute('rented_by'),
            'rented_at' => RentalDate::iso($this->getAttribute('rented_at')),
            'due_on' => RentalDate::date($this->getAttribute('due_on')),
            'status' => $this->getAttribute('status'),
            'deposit_cents' => (int) $this->getAttribute('deposit_cents'),
            'charge_id' => $this->getAttribute('charge_id') === null
                ? null
                : (int) $this->getAttribute('charge_id'),
            'notes' => $this->getAttribute('notes'),
            'created_at' => RentalDate::iso($this->getAttribute('created_at')),
            'updated_at' => RentalDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
