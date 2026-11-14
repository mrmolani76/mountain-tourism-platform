<?php

namespace Modules\Reports\App\Support;

use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Modules\Core\App\Enums\StaffRole;
use Modules\Core\App\Support\StaffAuthz;

/** Module I role gates: owner/manager for dashboard and reports. */
final class ReportsAuthz
{
    public static function denyUnlessOwnerOrManager(): ?JsonResponse
    {
        return self::denyUnlessRole([
            StaffRole::OWNER,
            StaffRole::MANAGER,
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
