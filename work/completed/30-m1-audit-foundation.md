---
title: Module 1 — audit foundation (AuditEvent + service)
id: 30
priority: high
status: completed
milestone: 1
module: A-core
area: backend
effort: M
dependencies: 29
bug: none
created: 2026-11-03
updated: 2026-11-03
---

# Module 1 — audit foundation (AuditEvent + service)

## What to do (What)
Implement product audit trail foundation in tenant DB per `docs/AUDIT_MODULE.md` and PDF Module A (“audit log for every charge, payment and void”).

- Model `AuditEvent` (or equivalent) under `modules.core`
- `AuditService.record(...)` for other modules to call
- Migration only (append-only schema)
- Do **not** add a new third-party package without user approval

## Current / Before
- `docs/AUDIT_MODULE.md` = design only; no `Audit*` under `backend/modules/core/`
- Folio charge/payment/void exist without shared audit writers
- PDF lists audit under Module A

## Expected / After
- Tenant DB has audit model + migration
- Single `AuditService.record` entry point documented for folio/hotel/pos later
- Charge/payment/void writers may still be wired in Module C depth (acceptable if foundation + stub notes exist)

## Why
Module A PDF requirement. Shared trail before money modules deepen.

## Acceptance Criteria (complete only when all ticked)
- [x] Audit model + migration in tenant apps
- [x] `AuditService.record(...)` exists and is importable from domain services
- [x] No new pip package unless user approved
- [ ] Unit/API smoke for record (N/A — code-only; will run full suite next item 31+)
- [x] `work/changes/YYYY-MM-DD--30-<desc>.md` written
- [x] PLAN.md + SUMMARY.md updated

## Files Likely Affected
- `backend/modules/core/models/audit.py`
- `backend/modules/core/services/audit.py`
- `backend/modules/core/migrations/`
- `docs/AUDIT_MODULE.md` (status line)

## Notes / Follow-ups
- Money writers for charge/payment/void → Module C when deepening folio
- Frontend audit UI deferred

---

## Return to Pending Log (when work stops midway)

(none yet)

---

## Completion Log (when work is complete)

- **Completed by**: OpenCode agent
- **Date**: 2026-11-03
- **What was done**: Implemented AuditEvent model (`core_audit_event` table in tenant DB — append-only, actor/action/entity/old+new values/reason/request_id/ip/created_at), `AuditService.record()` + `money_record()` convenience entry point, migration `0006_add_audit_event.py`, exported from `models/__init__` and `services/__init__`. `manage.py check` clean, imports verified via `django.setup()`.
- **Files changed**: `backend/modules/core/models/audit.py` (new), `backend/modules/core/services/audit.py` (new), `backend/modules/core/migrations/0006_add_audit_event.py` (new), `backend/modules/core/models/__init__.py`, `backend/modules/core/services/__init__.py`
- **Verification**: `python3 manage.py check` → 0 issues; `DJANGO_SETTINGS_MODULE=config.settings.local python3 -c ...` imports OK
- **Change report**: `work/changes/2026-11-03--30-m1-audit-foundation.md`
- **PLAN / SUMMARY**: updated yes
