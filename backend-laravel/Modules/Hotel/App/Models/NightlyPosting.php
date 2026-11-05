<?php

namespace Modules\Hotel\App\Models;

use App\Models\BaseModel;
use Modules\Hotel\App\Support\ApiDate;

/** One row per stay per posted night: the retry guard for nightly posting. */
class NightlyPosting extends BaseModel
{
    public const STATUS_POSTED = 'posted';

    public const STATUS_SKIPPED = 'skipped';

    public const STATUS_FAILED = 'failed';

    public const UPDATED_AT = null;

    protected $connection = 'tenant';

    protected $table = 'hotel_nightly_posting';

    protected $fillable = [
        'stay_id', 'posted_on', 'charges_count', 'room_charge_cents',
        'board_charge_cents', 'status', 'message',
    ];

    protected $casts = [
        'stay_id' => 'integer',
        'posted_on' => 'date',
        'charges_count' => 'integer',
        'room_charge_cents' => 'integer',
        'board_charge_cents' => 'integer',
        'created_at' => 'datetime',
    ];

    public function stay()
    {
        return $this->belongsTo(Stay::class, 'stay_id');
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'stay_id' => (int) $this->getAttribute('stay_id'),
            'posted_on' => ApiDate::date($this->getAttribute('posted_on')),
            'charges_count' => (int) $this->getAttribute('charges_count'),
            'room_charge_cents' => (int) $this->getAttribute('room_charge_cents'),
            'board_charge_cents' => (int) $this->getAttribute('board_charge_cents'),
            'status' => $this->getAttribute('status'),
            'message' => $this->getAttribute('message'),
            'created_at' => ApiDate::iso($this->getAttribute('created_at')),
        ];
    }
}
