<?php

namespace Modules\Pos\App\Models;

use App\Models\BaseModel;
use Modules\Pos\App\Support\PosDate;

class BoardClaim extends BaseModel
{
    public const STATUS_CLAIMED = 'claimed';

    public const STATUS_VOID = 'void';

    public const MEALS = ['breakfast', 'lunch', 'dinner'];

    public const UPDATED_AT = null;

    protected $connection = 'tenant';

    protected $table = 'pos_board_claim';

    protected $fillable = [
        'stay_id', 'order_id', 'board_plan_id', 'service_date',
        'meal', 'status', 'claimed_by',
    ];

    protected $casts = [
        'stay_id' => 'integer',
        'order_id' => 'integer',
        'board_plan_id' => 'integer',
        'service_date' => 'date',
        'claimed_by' => 'integer',
        'created_at' => 'datetime',
    ];

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'stay_id' => (int) $this->getAttribute('stay_id'),
            'order_id' => $this->getAttribute('order_id') === null
                ? null
                : (int) $this->getAttribute('order_id'),
            'board_plan_id' => (int) $this->getAttribute('board_plan_id'),
            'service_date' => PosDate::date($this->getAttribute('service_date')),
            'meal' => $this->getAttribute('meal'),
            'status' => $this->getAttribute('status'),
            'claimed_by' => $this->getAttribute('claimed_by') === null
                ? null
                : (int) $this->getAttribute('claimed_by'),
            'created_at' => PosDate::iso($this->getAttribute('created_at')),
        ];
    }
}
