<?php

namespace Modules\Hotel\App\Models;

use App\Models\BaseModel;
use Modules\Hotel\App\Support\ApiDate;

/**
 * Four PDF board plans. `inclusions` is the meal entitlement list Module D reads:
 * an included meal must not be charged again at the POS.
 */
class BoardPlan extends BaseModel
{
    public const ROOM_ONLY = 'room_only';

    public const BED_BREAKFAST = 'bed_breakfast';

    public const HALF_BOARD = 'half_board';

    public const FULL_BOARD = 'full_board';

    public const CODES = [
        self::ROOM_ONLY => 'Room Only',
        self::BED_BREAKFAST => 'Bed & Breakfast',
        self::HALF_BOARD => 'Half Board',
        self::FULL_BOARD => 'Full Board',
    ];

    /** Default meal entitlements per PDF board plan meaning. */
    public const DEFAULT_INCLUSIONS = [
        self::ROOM_ONLY => [],
        self::BED_BREAKFAST => ['breakfast'],
        self::HALF_BOARD => ['breakfast', 'dinner'],
        self::FULL_BOARD => ['breakfast', 'lunch', 'dinner'],
    ];

    protected $connection = 'tenant';

    protected $table = 'hotel_board_plan';

    protected $fillable = ['code', 'name', 'price_per_person_cents', 'inclusions', 'is_active'];

    protected $casts = [
        'price_per_person_cents' => 'integer',
        'inclusions' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $plan) {
            if ($plan->getAttribute('inclusions') === null) {
                $plan->setAttribute(
                    'inclusions',
                    self::DEFAULT_INCLUSIONS[$plan->getAttribute('code')] ?? []
                );
            }
        });
    }

    /** @return list<string> */
    public function includedMeals(): array
    {
        $stored = $this->getAttribute('inclusions');
        if (is_array($stored)) {
            return array_values($stored);
        }

        return self::DEFAULT_INCLUSIONS[$this->getAttribute('code')] ?? [];
    }

    public function includesMeal(string $meal): bool
    {
        return in_array($meal, $this->includedMeals(), true);
    }

    public function toApiArray(): array
    {
        return [
            'id' => (int) $this->getKey(),
            'code' => $this->getAttribute('code'),
            'name' => $this->getAttribute('name'),
            'price_per_person_cents' => (int) $this->getAttribute('price_per_person_cents'),
            'inclusions' => $this->includedMeals(),
            'is_active' => (bool) $this->getAttribute('is_active'),
            'created_at' => ApiDate::iso($this->getAttribute('created_at')),
            'updated_at' => ApiDate::iso($this->getAttribute('updated_at')),
        ];
    }
}
