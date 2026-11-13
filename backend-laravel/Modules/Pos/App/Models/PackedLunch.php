<?php

namespace Modules\Pos\App\Models;

use App\Models\BaseModel;
use Modules\Pos\App\Support\PosDate;

class PackedLunch extends BaseModel
{
    public const STATUS_REQUESTED = 'requested';

    public const STATUS_PREPARED = 'prepared';

    public const STATUS_COLLECTED = 'collected';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_REQUESTED,
        self::STATUS_PREPARED,
        self::STATUS_COLLECTED,
        self::STATUS_CANCELLED,
    ];

    protected $connection = 'tenant';

    protected $table = 'pos_packed_lunch';

    protected $fillable = [
        'outlet_id', 'stay_id', 'guest_name', 'for_date',
        'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'outlet_id' => 'integer',
        'stay_id' => 'integer',
        'for_date' => 'date',
        'created_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(PackedLunchItem::class, 'packed_lunch_id');
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'outlet_id' => (int) $this->getAttribute('outlet_id'),
            'stay_id' => $this->getAttribute('stay_id') === null
                ? null
                : (int) $this->getAttribute('stay_id'),
            'guest_name' => $this->getAttribute('guest_name'),
            'for_date' => PosDate::date($this->getAttribute('for_date')),
            'status' => $this->getAttribute('status'),
            'notes' => $this->getAttribute('notes'),
            'created_by' => $this->getAttribute('created_by') === null
                ? null
                : (int) $this->getAttribute('created_by'),
            'created_at' => PosDate::iso($this->getAttribute('created_at')),
            'updated_at' => PosDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
