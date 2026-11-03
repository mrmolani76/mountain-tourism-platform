<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tenant staff user (audit §3: core_staff). Lives in the tenant DB and is NOT
 * the application AUTH user (same severity as Django: Staff is a plain model,
 * passwords hashed, is_authenticated/is_anonymous were properties).
 */
class Staff extends Model
{
    public const UPDATED_AT = null;

    protected $connection = 'tenant';

    protected $table = 'core_staff';

    protected static function booted(): void
    {
        static::creating(function (self $staff) {
            $staff->setAttribute('created_at', $staff->getAttribute('created_at') ?: now());
        });
    }

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_OWNER = 'owner';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_FRONT_DESK = 'front_desk';
    public const ROLE_CASHIER = 'cashier';
    public const ROLE_RENTAL_DESK = 'rental_desk';
    public const ROLE_SERVICE_STAFF = 'service_staff';
    public const ROLE_TRANSPORT_DESK = 'transport_desk';
    public const ROLE_LIFT_OPERATOR = 'lift_operator';

    /** Display labels — parity with Django StaffRole.TextChoices. */
    public const ROLE_LABELS = [
        self::ROLE_SUPER_ADMIN => 'Super Admin',
        self::ROLE_OWNER => 'Owner',
        self::ROLE_MANAGER => 'Manager',
        self::ROLE_FRONT_DESK => 'Front Desk',
        self::ROLE_CASHIER => 'Cashier',
        self::ROLE_RENTAL_DESK => 'Rental Desk',
        self::ROLE_SERVICE_STAFF => 'Service Staff',
        self::ROLE_TRANSPORT_DESK => 'Transport Desk',
        self::ROLE_LIFT_OPERATOR => 'Lift Operator',
    ];

    protected $fillable = ['email', 'password', 'role', 'is_active', 'last_login'];

    protected $hidden = ['password'];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function roleDisplay(): string
    {
        return self::ROLE_LABELS[$this->getAttribute('role')] ?? $this->getAttribute('role');
    }

    public function canResetPasswords(): bool
    {
        return in_array($this->getAttribute('role'), [self::ROLE_OWNER, self::ROLE_MANAGER], true);
    }

    public function isOwner(): bool
    {
        return $this->getAttribute('role') === self::ROLE_OWNER;
    }

    public function isManagerPlus(): bool
    {
        return in_array($this->getAttribute('role'), [self::ROLE_OWNER, self::ROLE_MANAGER], true);
    }
}
