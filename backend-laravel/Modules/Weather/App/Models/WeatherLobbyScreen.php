<?php

namespace Modules\Weather\App\Models;

use App\Models\BaseModel;
use Modules\Weather\App\Support\WeatherDate;

class WeatherLobbyScreen extends BaseModel
{
    protected $connection = 'tenant';

    protected $table = 'weather_lobby_screen';

    protected $fillable = [
        'name', 'public_token', 'building_id', 'is_active',
    ];

    protected $casts = [
        'building_id' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'name' => $this->getAttribute('name'),
            'public_token' => $this->getAttribute('public_token'),
            'building_id' => $this->getAttribute('building_id') === null
                ? null
                : (int) $this->getAttribute('building_id'),
            'is_active' => (bool) $this->getAttribute('is_active'),
            'created_at' => WeatherDate::iso($this->getAttribute('created_at')),
            'updated_at' => WeatherDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
