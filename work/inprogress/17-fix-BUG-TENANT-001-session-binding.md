---
title: Fix BUG-TENANT-001 — bind staff session to issuing tenant
id: 17
priority: high
status: inprogress
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
- [ ] Login writes tenant binding to session
- [ ] Auth rejects missing/mismatched binding with 401 before Staff lookup
- [ ] New cross-host replay test(s) in tests/api/test_auth.py
- [ ] `manage.py check` clean + py_compile
- [ ] Bug report updated to VERIFIED/COMPLETED + INDEX + SUMMARY sync
- [ ] Change report written
- [ ] PLAN/SUMMARY updated

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

- **Completed by**:
- **Date**:
- **What was done**:
- **Files changed**:
- **Verification**:
- **Change report**:
- **PLAN / SUMMARY**:
