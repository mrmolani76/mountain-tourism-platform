<?php

namespace Modules\Hotel\App\Models;

use App\Models\BaseModel;
use Modules\Core\App\Models\Season;
use Modules\Hotel\App\Support\ApiDate;

/** Room night price per tenant season. Amount is integer minor units. */
class RoomSeasonPrice extends BaseModel
{
    protected $connection = 'tenant';

    protected $table = 'hotel_room_season_price';

    protected $fillable = ['room_type_id', 'season_id', 'amount_cents'];

    protected $casts = [
        'room_type_id' => 'integer',
        'season_id' => 'integer',
        'amount_cents' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function season()
    {
        return $this->belongsTo(Season::class, 'season_id');
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'room_type_id' => (int) $this->getAttribute('room_type_id'),
            'season_id' => (int) $this->getAttribute('season_id'),
            'amount_cents' => (int) $this->getAttribute('amount_cents'),
            'created_at' => ApiDate::iso($this->getAttribute('created_at')),
            'updated_at' => ApiDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
