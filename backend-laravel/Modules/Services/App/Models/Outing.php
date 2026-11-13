<?php

namespace Modules\Services\App\Models;

use App\Models\BaseModel;
use Modules\Services\App\Support\ServicesDate;

class Outing extends BaseModel
{
    public const STATUS_OPEN = 'open';

    public const STATUS_OUT = 'out';

    public const STATUS_CLOSED = 'closed';

    protected $connection = 'tenant';

    protected $table = 'service_outing';

    protected $fillable = [
        'slot_id', 'status', 'departed_at', 'returned_at', 'notes',
    ];

    protected $casts = [
        'slot_id' => 'integer',
        'departed_at' => 'datetime',
        'returned_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function participants()
    {
        return $this->hasMany(OutingParticipant::class, 'outing_id');
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'slot_id' => (int) $this->getAttribute('slot_id'),
            'status' => $this->getAttribute('status'),
            'departed_at' => ServicesDate::iso($this->getAttribute('departed_at')),
            'returned_at' => ServicesDate::iso($this->getAttribute('returned_at')),
            'notes' => $this->getAttribute('notes'),
            'created_at' => ServicesDate::iso($this->getAttribute('created_at')),
            'updated_at' => ServicesDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
