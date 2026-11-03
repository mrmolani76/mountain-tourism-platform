<?php

namespace Modules\Saas\Enums;

/**
 * Central platform roles — Mountain MVP Scope v1 §3 "Super admin".
 * Lives on saas_user (central DB), never on tenant core_staff.
 */
final class SaasRole
{
    public const SUPER_ADMIN = 'super_admin';

    public const LABELS = [
        self::SUPER_ADMIN => 'Super Admin',
    ];

    public const WORKS_IN = [
        self::SUPER_ADMIN => 'Super admin page',
    ];

    /** @return list<string> */
    public static function codes(): array
    {
        return array_keys(self::LABELS);
    }
}
