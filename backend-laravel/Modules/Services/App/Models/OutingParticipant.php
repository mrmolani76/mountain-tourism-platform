<?php

namespace Modules\Services\App\Models;

use App\Models\BaseModel;
use Modules\Services\App\Support\ServicesDate;

class OutingParticipant extends BaseModel
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_OUT = 'out';

    public const STATUS_BACK = 'back';

    public const STATUS_MISSING = 'missing';

    public $timestamps = false;

    protected $connection = 'tenant';

    protected $table = 'service_outing_participant';

    protected $fillable = [
        'outing_id', 'booking_participant_id', 'out_at', 'back_at', 'status',
    ];

    protected $casts = [
        'outing_id' => 'integer',
        'booking_participant_id' => 'integer',
        'out_at' => 'datetime',
        'back_at' => 'datetime',
    ];

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'outing_id' => (int) $this->getAttribute('outing_id'),
            'booking_participant_id' => (int) $this->getAttribute('booking_participant_id'),
            'out_at' => ServicesDate::iso($this->getAttribute('out_at')),
            'back_at' => ServicesDate::iso($this->getAttribute('back_at')),
            'status' => $this->getAttribute('status'),
        ];
    }
}
