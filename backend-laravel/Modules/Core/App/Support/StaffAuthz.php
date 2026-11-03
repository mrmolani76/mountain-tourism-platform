<?php

namespace Modules\Core\App\Support;

use App\Support\Http\Envelope;
use Illuminate\Http\JsonResponse;
use Modules\Core\App\Models\Staff;

/**
 * Server-side role / outlet gates for tenant Core APIs (PDF §7 + deep analysis §6.3).
 * UI hiding is never enough — call these from controllers.
 */
final class StaffAuthz
{
    public static function staff(): Staff
    {
        return app('staff');
    }

    public static function denyUnlessOwner(): ?JsonResponse
    {
        if (! self::staff()->isOwner()) {
            return Envelope::errorJson('error', 'You do not have permission to perform this action.', [], 403);
        }

        return null;
    }

    /** Owner or Manager (password reset, voids later). */
    public static function denyUnlessManagerPlus(): ?JsonResponse
    {
        if (! self::staff()->isManagerPlus()) {
            return Envelope::errorJson('error', 'You do not have permission to perform this action.', [], 403);
        }

        return null;
    }

    /**
     * Owner always OK. Others need an explicit StaffOutletAccess row.
     */
    public static function denyUnlessOutletAccess(int $outletId): ?JsonResponse
    {
        $staff = self::staff();
        if ($staff->isOwner()) {
            return null;
        }

        if (! $staff->hasOutletAccess($outletId)) {
            return Envelope::errorJson('error', 'You do not have permission to perform this action.', [], 403);
        }

        return null;
    }
}
