<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Enums\StaffRole;

class Staff extends Model
{
    public const UPDATED_AT = null;

    protected $connection = 'tenant';

    protected $table = 'core_staff';

    /** @deprecated Use StaffRole::* */
    public const ROLE_OWNER = StaffRole::OWNER;

    /** @deprecated Use StaffRole::* */
    public const ROLE_MANAGER = StaffRole::MANAGER;

    /** @deprecated Use StaffRole::* */
    public const ROLE_FRONT_DESK = StaffRole::FRONT_DESK;

    /** @deprecated Use StaffRole::* */
    public const ROLE_CASHIER = StaffRole::CASHIER;

    /** @deprecated Use StaffRole::* */
    public const ROLE_RENTAL_DESK = StaffRole::RENTAL_DESK;

    /** @deprecated Use StaffRole::* */
    public const ROLE_SERVICE_STAFF = StaffRole::SERVICE_STAFF;

    /** @deprecated Use StaffRole::* */
    public const ROLE_TRANSPORT_DESK = StaffRole::TRANSPORT_DESK;

    /** @deprecated Use StaffRole::* */
    public const ROLE_LIFT_OPERATOR = StaffRole::LIFT_OPERATOR;

    /** @deprecated Use StaffRole::LABELS */
    public const ROLE_LABELS = StaffRole::LABELS;

    protected static function booted(): void
    {
        static::creating(function (self $staff) {
            $staff->setAttribute('created_at', $staff->getAttribute('created_at') ?: now());
        });
    }

    protected $fillable = ['email', 'password', 'role', 'is_active', 'last_login'];

    protected $hidden = ['password'];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function roleDisplay(): string
    {
        return StaffRole::label((string) $this->getAttribute('role'));
    }

    public function canResetPasswords(): bool
    {
        return StaffRole::canResetPasswords((string) $this->getAttribute('role'));
    }

    public function isOwner(): bool
    {
        return $this->getAttribute('role') === StaffRole::OWNER;
    }

    public function isManagerPlus(): bool
    {
        return in_array($this->getAttribute('role'), [StaffRole::OWNER, StaffRole::MANAGER], true);
    }

    public function outletAccess()
    {
        return $this->hasMany(StaffOutletAccess::class, 'staff_id');
    }
}
