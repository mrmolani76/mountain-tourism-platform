<?php

namespace App\Traits;

use Illuminate\Support\Facades\Event;
use OwenIt\Auditing\Events\AuditCustom;

/**
 * eQMS complere/app/Traits/CustomAuditable.php — owen-it Auditable + relation audit dispatch.
 */
trait CustomAuditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * @param  \Illuminate\Support\Collection  $old
     * @param  \Illuminate\Support\Collection  $new
     */
    private function dispatchRelationAuditEvent(string $relationName, string $event, $old, $new): void
    {
        $this->auditCustomOld[$relationName] = $old->toArray();
        $this->auditCustomNew[$relationName] = $new->toArray();

        if (
            empty($this->auditCustomOld[$relationName])
            && empty($this->auditCustomNew[$relationName])
        ) {
            $this->auditCustomOld = $this->auditCustomNew = [];
        }

        $this->auditEvent = $event;
        $this->isCustomEvent = true;
        Event::dispatch(AuditCustom::class, [$this]);
        $this->auditCustomOld = $this->auditCustomNew = [];
        $this->isCustomEvent = false;
    }
}
