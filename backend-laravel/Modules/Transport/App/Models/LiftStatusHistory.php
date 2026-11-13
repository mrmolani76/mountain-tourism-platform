<?php

namespace Modules\Transport\App\Models;

use App\Models\BaseModel;
use DomainException;
use Modules\Transport\App\Support\TransportDate;

class LiftStatusHistory extends BaseModel
{
    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_LIMITED = 'limited';

    public const STATUS_WEATHER_HOLD = 'weather_hold';

    public const STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_CLOSED,
        self::STATUS_LIMITED,
        self::STATUS_WEATHER_HOLD,
    ];

    public const UPDATED_AT = null;

    protected $connection = 'tenant';

    protected $table = 'transport_lift_status_history';

    protected $fillable = [
        'lift_id', 'status', 'reason', 'changed_by',
    ];

    protected $casts = [
        'lift_id' => 'integer',
        'changed_by' => 'integer',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new DomainException('Lift status history is append-only.');
        });

        static::deleting(function () {
            throw new DomainException('Lift status history cannot be deleted.');
        });
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'lift_id' => (int) $this->getAttribute('lift_id'),
            'status' => $this->getAttribute('status'),
            'reason' => $this->getAttribute('reason'),
            'changed_by' => $this->getAttribute('changed_by') === null
                ? null
                : (int) $this->getAttribute('changed_by'),
            'created_at' => TransportDate::iso($this->getAttribute('created_at')),
        ];
    }
}
