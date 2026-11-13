<?php

namespace Modules\Services\App\Support;

use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Modules\Core\App\Enums\StaffRole;
use Modules\Core\App\Support\StaffAuthz;

final class ServicesAuthz
{
    public static function denyUnlessManagerPlus(): ?JsonResponse
    {
        return StaffAuthz::denyUnlessManagerPlus();
    }

    public static function denyUnlessServiceDesk(): ?JsonResponse
    {
        return self::denyUnlessRole([
            StaffRole::OWNER,
            StaffRole::MANAGER,
            StaffRole::FRONT_DESK,
            StaffRole::SERVICE_STAFF,
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
