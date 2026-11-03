<?php

namespace Modules\Core\App\Enums;

/**
 * Tenant staff roles — Mountain MVP Scope v1 §3.
 *
 * Super admin is NOT a staff role; it is Modules\Saas\App\Models\SaasUser on the central DB.
 * Drivers do not log in in MVP (printed passenger list only).
 */
final class StaffRole
{
    public const OWNER = 'owner';
    public const MANAGER = 'manager';
    public const FRONT_DESK = 'front_desk';
    public const CASHIER = 'cashier';
    public const RENTAL_DESK = 'rental_desk';
    public const SERVICE_STAFF = 'service_staff';
    public const TRANSPORT_DESK = 'transport_desk';
    public const LIFT_OPERATOR = 'lift_operator';

    /** @var array<string, string> code => PDF display label */
    public const LABELS = [
        self::OWNER => 'Owner',
        self::MANAGER => 'Manager',
        self::FRONT_DESK => 'Front Desk',
        self::CASHIER => 'Cashier',
        self::RENTAL_DESK => 'Rental Desk',
        self::SERVICE_STAFF => 'Service Staff',
        self::TRANSPORT_DESK => 'Transport Desk',
        self::LIFT_OPERATOR => 'Lift Operator',
    ];

    /** @var array<string, string> code => PDF "Works in" */
    public const WORKS_IN = [
        self::OWNER => 'Everything',
        self::MANAGER => 'Assigned outlets',
        self::FRONT_DESK => 'Hotel, chalets, hut',
        self::CASHIER => 'Restaurants, hut, shop',
        self::RENTAL_DESK => 'Rental shop',
        self::SERVICE_STAFF => 'Spa, sauna, sports, ski school',
        self::TRANSPORT_DESK => 'Transport',
        self::LIFT_OPERATOR => 'Lift stations',
    ];

    /** @return list<string> */
    public static function codes(): array
    {
        return array_keys(self::LABELS);
    }

    public static function isValid(string $code): bool
    {
        return isset(self::LABELS[$code]);
    }

    public static function label(string $code): string
    {
        return self::LABELS[$code] ?? $code;
    }

    /** Owner + Manager may reset staff passwords (PDF §5.A). */
    public static function canResetPasswords(string $code): bool
    {
        return in_array($code, [self::OWNER, self::MANAGER], true);
    }
}
