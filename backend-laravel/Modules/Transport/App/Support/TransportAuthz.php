<?php

namespace Modules\Transport\App\Support;

use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Modules\Core\App\Enums\StaffRole;
use Modules\Core\App\Support\StaffAuthz;

/** Module G role gates: transport desk, lift operator, front desk. */
final class TransportAuthz
{
    public static function denyUnlessManagerPlus(): ?JsonResponse
    {
        return StaffAuthz::denyUnlessManagerPlus();
    }

    public static function denyUnlessTransportDesk(): ?JsonResponse
    {
        return self::denyUnlessRole([
            StaffRole::OWNER,
            StaffRole::MANAGER,
            StaffRole::TRANSPORT_DESK,
            StaffRole::FRONT_DESK,
        ]);
    }

    public static function denyUnlessLiftOperator(): ?JsonResponse
    {
        return self::denyUnlessRole([
            StaffRole::OWNER,
            StaffRole::MANAGER,
            StaffRole::LIFT_OPERATOR,
            StaffRole::TRANSPORT_DESK,
            StaffRole::FRONT_DESK,
        ]);
    }

    public static function denyUnlessPassSeller(): ?JsonResponse
    {
        return self::denyUnlessRole([
            StaffRole::OWNER,
            StaffRole::MANAGER,
            StaffRole::FRONT_DESK,
            StaffRole::CASHIER,
            StaffRole::TRANSPORT_DESK,
        ]);
    }

    /**
     * @param  list<string>  $roles
     */
    private static function denyUnlessRole(array $roles): ?JsonResponse
    {
        if (in_array((string) StaffAuthz::staff()->getAttribute('role'), $roles, true)) {
            return null;
        }

        return Envelope::errorJson('error', 'You do not have permission to perform this action.', [], 403);
    }
}
