<?php

namespace Modules\Hotel\App\Models;

use App\Models\BaseModel;
use Modules\Hotel\App\Support\ApiDate;

class HutBed extends BaseModel
{
    protected $connection = 'tenant';

    protected $table = 'hotel_hut_bed';

    protected $fillable = ['hut_dormitory_id', 'bed_number', 'is_active'];

    protected $casts = [
        'hut_dormitory_id' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function dormitory()
    {
        return $this->belongsTo(HutDormitory::class, 'hut_dormitory_id');
    }

    public function unitLabel(): string
    {
        return (string) $this->getAttribute('bed_number');
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'hut_dormitory_id' => (int) $this->getAttribute('hut_dormitory_id'),
            'bed_number' => $this->getAttribute('bed_number'),
            'is_active' => (bool) $this->getAttribute('is_active'),
            'created_at' => ApiDate::iso($this->getAttribute('created_at')),
            'updated_at' => ApiDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
