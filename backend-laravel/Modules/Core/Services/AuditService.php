<?php

namespace Modules\Core\Services;

use Modules\Core\Models\AuditEvent;

/** Port of Django modules.core.services.audit.AuditService */
class AuditService
{
    public function record(
        string $action,
        string $entityType,
        int $entityId,
        ?int $actorStaffId = null,
        ?string $reason = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $requestId = null,
        ?string $ip = null,
    ): AuditEvent {
        return AuditEvent::on(config('tenancy.connection_name'))->create([
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'actor_staff_id' => $actorStaffId,
            'reason' => $reason ?? '',
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'request_id' => $requestId ?? '',
            'ip' => $ip,
        ]);
    }
}
