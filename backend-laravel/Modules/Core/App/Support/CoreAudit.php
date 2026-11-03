<?php

namespace Modules\Core\App\Support;

use Modules\Core\App\Services\AuditService;

/**
 * Thin helper so Core controllers can write AuditEvent without duplicating actor/IP wiring.
 */
final class CoreAudit
{
    public static function record(
        string $action,
        string $entityType,
        int $entityId,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $reason = null,
    ): void {
        $actorId = null;
        if (app()->bound('staff')) {
            $actorId = (int) app('staff')->getKey();
        }

        app(AuditService::class)->record(
            $action,
            $entityType,
            $entityId,
            actorStaffId: $actorId,
            reason: $reason,
            oldValues: $oldValues,
            newValues: $newValues,
            requestId: request()?->header('X-Request-Id'),
            ip: request()?->ip(),
        );
    }
}
