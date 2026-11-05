<?php

namespace Modules\Hotel\App\Support;

use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Modules\Core\App\Enums\StaffRole;
use Modules\Core\App\Support\StaffAuthz;

/**
 * Server-side role gates for Module B (PDF §5.B roles):
 * Owner configures inventory/pricing; Front Desk works reservations, check-in/out.
 */
final class HotelAuthz
{
    public static function denyUnlessOwner(): ?JsonResponse
    {
        return StaffAuthz::denyUnlessOwner();
    }

    public static function denyUnlessManagerPlus(): ?JsonResponse
    {
        return StaffAuthz::denyUnlessManagerPlus();
    }

    public static function denyUnlessFrontDesk(): ?JsonResponse
    {
        return self::denyUnlessRole([StaffRole::OWNER, StaffRole::MANAGER, StaffRole::FRONT_DESK]);
    }

    /** Front desk plus cashier: folio read / charge-to-room / payment. */
    public static function denyUnlessFolioClerk(): ?JsonResponse
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
