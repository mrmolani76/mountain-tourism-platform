<?php

namespace Modules\Rental\App\Models;

use App\Models\BaseModel;
use Modules\Rental\App\Support\RentalDate;

class SeasonPrice extends BaseModel
{
    protected $connection = 'tenant';

    protected $table = 'rental_season_price';

    protected $fillable = ['equipment_type_id', 'season_id', 'day_rate_cents'];

    protected $casts = [
        'equipment_type_id' => 'integer',
        'season_id' => 'integer',
        'day_rate_cents' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'equipment_type_id' => (int) $this->getAttribute('equipment_type_id'),
            'season_id' => (int) $this->getAttribute('season_id'),
            'day_rate_cents' => (int) $this->getAttribute('day_rate_cents'),
            'created_at' => RentalDate::iso($this->getAttribute('created_at')),
            'updated_at' => RentalDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
