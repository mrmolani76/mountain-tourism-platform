<?php

namespace Modules\Services\App\Models;

use App\Models\BaseModel;

class BookingParticipant extends BaseModel
{
    public $timestamps = false;

    protected $connection = 'tenant';

    protected $table = 'service_booking_participant';

    protected $fillable = ['booking_id', 'name', 'is_lead', 'checked_in'];

    protected $casts = [
        'booking_id' => 'integer',
        'is_lead' => 'boolean',
        'checked_in' => 'boolean',
    ];

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'booking_id' => (int) $this->getAttribute('booking_id'),
            'name' => $this->getAttribute('name'),
            'is_lead' => (bool) $this->getAttribute('is_lead'),
            'checked_in' => (bool) $this->getAttribute('checked_in'),
        ];
    }
}
