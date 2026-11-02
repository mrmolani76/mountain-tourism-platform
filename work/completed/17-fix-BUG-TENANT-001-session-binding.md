---
title: Fix BUG-TENANT-001 — bind staff session to issuing tenant
id: 17
priority: high
status: completed
milestone: 1
module: A-core
area: backend
effort: S
dependencies: 06
bug: BUG-TENANT-001
created: 2026-11-02
updated: 2026-11-02
---

# Fix BUG-TENANT-001 — bind staff session to issuing tenant

**Bug report**: [`work/bugs/BUG-TENANT-001-cross-host-session-identity.md`](../bugs/BUG-TENANT-001-cross-host-session-identity.md)

## What to do (What)
Store the issuing tenant binding (ID + slug) in the staff session at login and reject requests where session binding does not match the resolved tenant, before any Staff lookup.

## Current / Before
`StaffSessionAuthentication.authenticate()` loads `Staff.objects.get(pk=session['staff_id'])` on whichever tenant DB the Host resolved, no tenant binding check. Session issued on tenant A can authenticate on tenant B host when pk collides.

## Expected / After
Login stores session `staff_tenant_id` + `staff_tenant_slug`. Authentication fails (401) when binding missing or mismatched vs current tenant. New tests cover cross-host replay denial.

## Why
BUG-TENANT-001: defense in depth for Rule 3 tenant isolation. Browser cookie scoping is not an API-level guarantee.

## Acceptance Criteria (complete only when all ticked)
- [x] Login writes tenant binding to session
- [x] Auth rejects missing/mismatched binding with 401 before Staff lookup
- [x] New cross-host replay test(s) in tests/api/test_auth.py
- [x] `manage.py check` clean + py_compile
- [x] Bug report updated to VERIFIED/COMPLETED + INDEX + SUMMARY sync
- [x] Change report written
- [x] PLAN/SUMMARY updated

## Files Likely Affected
- `backend/modules/core/auth.py`, `backend/modules/core/views/api.py`, `backend/tests/api/test_auth.py`, `work/bugs/*`, kanban files

## Notes / Follow-ups
- Code-only test convention: execution on setup machine; runtime smoke on Docker stack possible.

---

## Return to Pending Log (when work stops midway)

Repeatable block (newest first):

### YYYY-MM-DD
- **Returned by**:
- **Reason**:
- **Next step**:

---

## Completion Log (when work is complete)

- **Completed by**: coding agent
- **Date**: 2026-11-02
- **What was done**: Implemented tenant binding in session at login (LoginView writes `staff_tenant_id` + `staff_tenant_slug`), added tenant binding check in `StaffSessionAuthentication.authenticate()` to reject mismatched/bindings with 401 before Staff lookup. Added cross-host replay test (`test_cross_host_session_replay_rejected`) and pk collision test (`test_cross_host_pk_collision_rejected`) and unbound session test (`test_unbound_session_rejected`) in `tests/api/test_auth.py`.
- **Files changed**:
  - `backend/modules/core/auth.py` — added tenant binding validation (lines 36-39)
  - `backend/modules/core/views/api.py` — added tenant binding write at login (lines 84-85)
  - `backend/tests/api/test_auth.py` — added 3 cross-tenant tests
- **Verification**: `manage.py check` clean; `py_compile` on all touched files OK
- **Change report**: `work/changes/2026-11-02--06-core-config-apis.md` (item 06, references BUG-TENANT-001 fix path)
- **PLAN / SUMMARY**: Item 17 moved to Completed in PLAN.md; BUG-TENANT-001 verified in INDEX.md; SUMMARY.md updated

---
