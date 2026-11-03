# Change Report: Module 1 audit foundation (item 30)

- **Date**: 2026-11-03
- **By**: OpenCode agent
- **Change ID**: 30

## 1. Summary
Implemented append-only audit trail foundation in the tenant DB per `docs/AUDIT_MODULE.md` and PDF Module A ("audit log for every charge, payment and void"). No new third-party package (custom model + service, per rules).

## 2. Before
- `docs/AUDIT_MODULE.md` was design-only; no `Audit*` model in `backend/modules/core/`
- Folio charge/payment/void exist without shared audit writers
- `backend/modules/core/models/` had no audit model; `services/` had no audit writer

## 3. After
- `backend/modules/core/models/audit.py` (new): `AuditEvent` model — `actor_staff_id`, `action` (create/update/void/login), `entity_type`, `entity_id`, `old_values`/`new_values` JSON, `reason`, `request_id`, `ip`, `created_at`; `db_table='core_audit_event'`; append-only guard (`save()` on existing pk raises, `delete()` raises); indexes on entity/action/actor/created_at
- `backend/modules/core/services/audit.py` (new): `AuditService.record(...)` single entry point; `money_record()` convenience (requires reason for Charge/Payment/Void)
- `backend/modules/core/migrations/0006_add_audit_event.py` (new): `CreateModel AuditEvent`
- `backend/modules/core/models/__init__.py` + `services/__init__.py`: export `AuditEvent`, `AuditService`
- `manage.py check` → 0 issues; imports verified via `django.setup()`

## 4. Reason
Module A PDF requirement — money/void audit trail must exist before folio money modules deepen (items 40–44).

## 5. Files Affected
- `backend/modules/core/models/audit.py` (new)
- `backend/modules/core/services/audit.py` (new)
- `backend/modules/core/migrations/0006_add_audit_event.py` (new)
- `backend/modules/core/models/__init__.py`, `backend/modules/core/services/__init__.py`
- `work/changes/2026-11-03--30-m1-audit-foundation.md`
- `work/completed/30-m1-audit-foundation.md`
- `work/PLAN.md`, `work/SUMMARY.md`, `work/MEMORY.md`

## 6. Verification / Status
- [x] `python3 manage.py check` → 0 issues
- [x] `DJANGO_SETTINGS_MODULE=config.settings.local` imports OK (`AuditEvent`, `AuditService`)
- [x] Migration `0006_add_audit_event.py` depends on `0005`, table `core_audit_event`
- [x] No new pip package
- [ ] Folio/hotel/pos charge/payment/void writers wired later (Module C depth items 40–44)
- [x] Change report written; PLAN/SUMMARY/MEMORY updated

## 7. Usage
```python
from modules.core.services import AuditService
AuditService.record(
    actor_staff_id=staff.id,
    action='void',
    entity_type='folio.Charge',
    entity_id=charge.pk,
    old_values={...},
    new_values={...},
    reason='manager-approved void',
)
```
