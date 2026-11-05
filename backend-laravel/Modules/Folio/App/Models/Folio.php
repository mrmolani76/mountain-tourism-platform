<?php

namespace Modules\Folio\App\Models;

use App\Models\BaseModel;
use Modules\Folio\App\Support\FolioDate;

/** One bill per stay. Opened at check-in, closed at checkout when balance is zero. */
class Folio extends BaseModel
{
    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    protected $connection = 'tenant';

    protected $table = 'folio_folio';

    protected $fillable = ['reference', 'status', 'currency', 'opened_at', 'closed_at'];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function charges()
    {
        return $this->hasMany(Charge::class, 'folio_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'folio_id');
    }

    public function voids()
    {
        return $this->hasMany(FolioVoid::class, 'folio_id');
    }

    public function isOpen(): bool
    {
        return $this->getAttribute('status') === self::STATUS_OPEN;
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'reference' => $this->getAttribute('reference'),
            'status' => $this->getAttribute('status'),
            'currency' => $this->getAttribute('currency'),
            'opened_at' => FolioDate::iso($this->getAttribute('opened_at')),
            'closed_at' => FolioDate::iso($this->getAttribute('closed_at')),
            'created_at' => FolioDate::iso($this->getAttribute('created_at')),
            'updated_at' => FolioDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
