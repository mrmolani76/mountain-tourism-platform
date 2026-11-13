<?php

namespace Modules\Pos\App\Support;

use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Modules\Core\App\Enums\StaffRole;
use Modules\Core\App\Support\StaffAuthz;

/**
 * Module D role gates (PDF §5.D):
 * Manager/Owner configure products; Cashier runs POS; Front Desk may assist.
 */
final class PosAuthz
{
    public static function denyUnlessManagerPlus(): ?JsonResponse
    {
        return StaffAuthz::denyUnlessManagerPlus();
    }

    public static function denyUnlessCashierPlus(): ?JsonResponse
    {
        return self::denyUnlessRole([
            StaffRole::OWNER,
            StaffRole::MANAGER,
            StaffRole::FRONT_DESK,
            StaffRole::CASHIER,
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
