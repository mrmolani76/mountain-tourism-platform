"""Audit trail events on the tenant DB.

Append-only — rows are never edited or deleted (Money Rule 5: like Charge/Payment).
One row per money/domain-changing action: charge, payment, void, ...
Written via AuditService.record(...) from services — never directly from views.
"""

import json
from django.db import models


class AuditEvent(models.Model):
    """One change event on a tenant entity."""

    ACTION_CREATE = 'create'
    ACTION_UPDATE = 'update'
    ACTION_VOID = 'void'
    ACTION_LOGIN = 'login'

    ACTION_CHOICES = [
        (ACTION_CREATE, 'Create'),
        (ACTION_UPDATE, 'Update'),
        (ACTION_VOID, 'Void'),
        (ACTION_LOGIN, 'Login'),
    ]

    actor_staff_id = models.IntegerField(null=True, blank=True)
    action = models.CharField(max_length=16, choices=ACTION_CHOICES)
    entity_type = models.CharField(max_length=128)  # e.g. 'folio.Charge'
    entity_id = models.IntegerField()
    old_values = models.JSONField(null=True, blank=True)
    new_values = models.JSONField(null=True, blank=True)
    reason = models.CharField(max_length=255, blank=True)
    request_id = models.CharField(max_length=128, blank=True)
    ip = models.GenericIPAddressField(null=True, blank=True)
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        db_table = 'core_audit_event'
        ordering = ['-created_at']
        indexes = [
            models.Index(fields=['entity_type', 'entity_id']),
            models.Index(fields=['actor_staff_id']),
            models.Index(fields=['created_at']),
        ]

    def __str__(self):
        return f'{self.action} {self.entity_type}#{self.entity_id} by {self.actor_staff_id}'

    def save(self, *args, **kwargs):
        # Audit rows are append-only — prevent edits/deletes via ORM.
        if self.pk is not None:
            raise RuntimeError('AuditEvent rows are append-only — do not edit.')
        super().save(*args, **kwargs)

    def delete(self, *args, **kwargs):
        raise RuntimeError('AuditEvent rows are append-only — do not delete.')
