<?php

namespace Modules\Services\App\Models;

use App\Models\BaseModel;

class SlotStaff extends BaseModel
{
    public $timestamps = false;

    protected $connection = 'tenant';

    protected $table = 'service_slot_staff';

    protected $fillable = ['slot_id', 'staff_id', 'role'];

    protected $casts = [
        'slot_id' => 'integer',
        'staff_id' => 'integer',
    ];

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'slot_id' => (int) $this->getAttribute('slot_id'),
            'staff_id' => (int) $this->getAttribute('staff_id'),
            'role' => $this->getAttribute('role'),
        ];
    }
}
