<?php

namespace Modules\Folio\App\Support;

use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Modules\Core\App\Enums\StaffRole;
use Modules\Core\App\Support\StaffAuthz;

/**
 * Server-side role gates for Module C (PDF §5.C):
 * Front desk + cashier charge/pay; Manager+ voids; Owner always allowed.
 */
final class FolioAuthz
{
    public static function denyUnlessManagerPlus(): ?JsonResponse
    {
        return StaffAuthz::denyUnlessManagerPlus();
    }

    /** Read folio, record payment, charge to room. */
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
     * Outlet staff who may post a charge to an open stay folio.
     * Cashier is the PDF primary; rental/service/transport desks also post later modules.
     */
    public static function denyUnlessChargeToRoom(): ?JsonResponse
    {
        return self::denyUnlessRole([
            StaffRole::OWNER,
            StaffRole::MANAGER,
            StaffRole::FRONT_DESK,
            StaffRole::CASHIER,
            StaffRole::RENTAL_DESK,
            StaffRole::SERVICE_STAFF,
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
