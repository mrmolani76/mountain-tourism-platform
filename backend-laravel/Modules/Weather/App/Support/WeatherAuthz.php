<?php

namespace Modules\Weather\App\Support;

use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Modules\Core\App\Enums\StaffRole;
use Modules\Core\App\Support\StaffAuthz;

/** Module H role gates: owner/manager CRUD; manager+ cancel-for-weather. */
final class WeatherAuthz
{
    /** Owner or manager may create/update points, limits, avalanche. */
    public static function denyUnlessOwnerOrManager(): ?JsonResponse
    {
        return self::denyUnlessRole([
            StaffRole::OWNER,
            StaffRole::MANAGER,
        ]);
    }

    /** Manager+ may cancel for weather (voids related folio charges). */
    public static function denyUnlessManagerPlus(): ?JsonResponse
    {
        return StaffAuthz::denyUnlessManagerPlus();
    }

    /** Staff who may list warnings / trigger fetch+evaluate. */
    public static function denyUnlessWeatherStaff(): ?JsonResponse
    {
        return self::denyUnlessRole([
            StaffRole::OWNER,
            StaffRole::MANAGER,
            StaffRole::FRONT_DESK,
            StaffRole::TRANSPORT_DESK,
            StaffRole::SERVICE_STAFF,
            StaffRole::LIFT_OPERATOR,
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
