<?php

namespace Tests\Unit\Modules\Core;

use Modules\Core\App\Models\AuditEvent;
use Modules\Core\App\Services\AuditService;
use Tests\Support\TenancyTestCase;

/**
 * Append-only audit foundation (Module 1 / deep analysis §11).
 */
class AuditEventTest extends TenancyTestCase
{
    public function test_audit_service_records_event(): void
    {
        $event = (new AuditService)->record(
            AuditEvent::ACTION_CREATE,
            'building',
            1,
            actorStaffId: $this->owner->getKey(),
            reason: 'seed',
            newValues: ['name' => 'Hut'],
        );

        $this->assertSame(AuditEvent::ACTION_CREATE, $event->action);
        $this->assertSame('building', $event->entity_type);
        $this->assertSame(1, (int) $event->entity_id);
        $this->assertFalse($event->wasRecentlyCreated === false && $event->exists === false);
        $this->assertTrue($event->exists);
    }

    public function test_audit_event_cannot_be_updated(): void
    {
        $event = (new AuditService)->record(
            AuditEvent::ACTION_VOID,
            'charge',
            99,
            actorStaffId: $this->owner->getKey(),
            reason: 'weather',
        );

        $this->expectException(\RuntimeException::class);
        $event->action = AuditEvent::ACTION_UPDATE;
        $event->save();
    }

    public function test_audit_event_cannot_be_deleted(): void
    {
        $event = (new AuditService)->record(
            AuditEvent::ACTION_LOGIN,
            'staff',
            (int) $this->owner->getKey(),
            actorStaffId: $this->owner->getKey(),
        );

        $this->expectException(\RuntimeException::class);
        $event->delete();
    }
}
