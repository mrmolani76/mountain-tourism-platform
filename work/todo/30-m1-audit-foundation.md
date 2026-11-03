---
title: Module 1 — audit foundation (AuditEvent + service)
id: 30
priority: high
status: todo
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
- [ ] Audit model + migration in tenant apps
- [ ] `AuditService.record(...)` exists and is importable from domain services
- [ ] No new pip package unless user approved
- [ ] Unit/API smoke for record (or N/A with reason if code-only and user forbids test run)
- [ ] `work/changes/YYYY-MM-DD--30-<desc>.md` written
- [ ] PLAN.md + SUMMARY.md updated

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

- **Completed by**:
- **Date**:
- **What was done**:
- **Files changed**:
- **Verification**:
- **Change report**:
- **PLAN / SUMMARY**:
