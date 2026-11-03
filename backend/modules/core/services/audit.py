"""Audit service — single entry point for product audit trail.

Called from domain services (folio, hotel, pos, …) after a money/domain change.
Records are append-only: actor, action, entity (+ old/new values), reason, context.
"""

from typing import Optional

from modules.core.models.audit import AuditEvent


class AuditService:
    """Append-only audit writer for tenant DB events."""

    @classmethod
    def record(
        cls,
        *,
        actor_staff_id: Optional[int] = None,
        action: str,
        entity_type: str,
        entity_id: int,
        old_values: Optional[dict] = None,
        new_values: Optional[dict] = None,
        reason: str = '',
        request_id: Optional[str] = None,
        ip: Optional[str] = None,
    ) -> AuditEvent:
        """Create an immutable audit row."""
        return AuditEvent.objects.create(
            actor_staff_id=actor_staff_id,
            action=action,
            entity_type=entity_type,
            entity_id=entity_id,
            old_values=old_values,
            new_values=new_values,
            reason=reason,
            request_id=request_id or '',
            ip=ip,
        )

    @classmethod
    def money_record(
        cls,
        *,
        actor_staff_id: int,
        action: str,
        entity_type: str,
        entity_id: int,
        old_values: Optional[dict] = None,
        new_values: Optional[dict] = None,
        reason: str,
        request_id: Optional[str] = None,
        ip: Optional[str] = None,
    ) -> AuditEvent:
        """Convenience for Charge/Payment/Void events — reason required."""
        if not reason:
            raise ValueError('Audit reason required for money events.')
        return cls.record(
            actor_staff_id=actor_staff_id,
            action=action,
            entity_type=entity_type,
            entity_id=entity_id,
            old_values=old_values,
            new_values=new_values,
            reason=reason,
            request_id=request_id,
            ip=ip,
        )
