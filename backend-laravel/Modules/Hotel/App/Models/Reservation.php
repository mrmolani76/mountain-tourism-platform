<?php

namespace Modules\Hotel\App\Models;

use App\Models\BaseModel;
use Carbon\CarbonImmutable;
use Modules\Hotel\App\Support\ApiDate;

class Reservation extends BaseModel
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_CHECKED_IN = 'checked_in';

    public const STATUS_CHECKED_OUT = 'checked_out';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_NO_SHOW = 'no_show';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_CHECKED_IN,
        self::STATUS_CHECKED_OUT,
        self::STATUS_CANCELLED,
        self::STATUS_NO_SHOW,
    ];

    public const ARRIVAL_MODES = ['car', 'train', 'plane', 'bus'];

    /** Statuses that keep accommodation blocked for their date range. */
    public const BLOCKING_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_CHECKED_IN,
    ];

    /** Statuses a check-in may start from. */
    public const CHECK_IN_STATUSES = [self::STATUS_PENDING, self::STATUS_CONFIRMED];

    protected $connection = 'tenant';

    protected $table = 'hotel_reservation';

    protected $fillable = [
        'reference', 'guest_name', 'guest_email', 'guest_phone', 'check_in_on', 'check_out_on',
        'adults', 'children', 'board_plan_id', 'deposit_cents', 'arrival_mode', 'arrival_time',
        'arrival_station', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'check_in_on' => 'date',
        'check_out_on' => 'date',
        'adults' => 'integer',
        'children' => 'integer',
        'board_plan_id' => 'integer',
        'deposit_cents' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function boardPlan()
    {
        return $this->belongsTo(BoardPlan::class, 'board_plan_id');
    }

    public function allocations()
    {
        return $this->hasMany(ReservationAccommodation::class, 'reservation_id');
    }

    public function stays()
    {
        return $this->hasMany(Stay::class, 'reservation_id');
    }

    public function nights(): int
    {
        $checkIn = CarbonImmutable::parse($this->getAttribute('check_in_on'));
        $checkOut = CarbonImmutable::parse($this->getAttribute('check_out_on'));

        return (int) $checkIn->diffInDays($checkOut);
    }

    public function guestCount(): int
    {
        return (int) $this->getAttribute('adults') + (int) $this->getAttribute('children');
    }

    public function blocksInventory(): bool
    {
        return in_array($this->getAttribute('status'), self::BLOCKING_STATUSES, true);
    }

    public function toApiArray(): array
    {
        $allocations = $this->relationLoaded('allocations')
            ? $this->getRelation('allocations')
            : $this->allocations()->get();

        return [
            'id' => (int) $this->getKey(),
            'reference' => $this->getAttribute('reference'),
            'guest_name' => $this->getAttribute('guest_name'),
            'guest_email' => $this->getAttribute('guest_email'),
            'guest_phone' => $this->getAttribute('guest_phone'),
            'check_in_on' => ApiDate::date($this->getAttribute('check_in_on')),
            'check_out_on' => ApiDate::date($this->getAttribute('check_out_on')),
            'nights' => $this->nights(),
            'adults' => (int) $this->getAttribute('adults'),
            'children' => (int) $this->getAttribute('children'),
            'board_plan_id' => $this->getAttribute('board_plan_id') === null
                ? null
                : (int) $this->getAttribute('board_plan_id'),
            'deposit_cents' => (int) $this->getAttribute('deposit_cents'),
            'arrival_mode' => $this->getAttribute('arrival_mode'),
            'arrival_time' => $this->getAttribute('arrival_time'),
            'arrival_station' => $this->getAttribute('arrival_station'),
            'status' => $this->getAttribute('status'),
            'notes' => $this->getAttribute('notes'),
            'allocations' => $allocations
                ->map(fn (ReservationAccommodation $a) => $a->toApiArray())
                ->values()
                ->all(),
            'created_at' => ApiDate::iso($this->getAttribute('created_at')),
            'updated_at' => ApiDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
