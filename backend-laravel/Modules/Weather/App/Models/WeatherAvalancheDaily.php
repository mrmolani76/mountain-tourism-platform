<?php

namespace Modules\Weather\App\Models;

use App\Models\BaseModel;
use Modules\Weather\App\Support\WeatherDate;

class WeatherAvalancheDaily extends BaseModel
{
    public const UPDATED_AT = null;

    protected $connection = 'tenant';

    protected $table = 'weather_avalanche_daily';

    protected $fillable = [
        'valid_on', 'level', 'source', 'notes', 'entered_by',
    ];

    protected $casts = [
        'valid_on' => 'date',
        'level' => 'integer',
        'entered_by' => 'integer',
        'created_at' => 'datetime',
    ];

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'valid_on' => WeatherDate::date($this->getAttribute('valid_on')),
            'level' => (int) $this->getAttribute('level'),
            'source' => $this->getAttribute('source'),
            'notes' => $this->getAttribute('notes'),
            'entered_by' => $this->getAttribute('entered_by') === null
                ? null
                : (int) $this->getAttribute('entered_by'),
            'created_at' => WeatherDate::iso($this->getAttribute('created_at')),
        ];
    }
}
