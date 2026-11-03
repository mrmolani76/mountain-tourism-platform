---
title: Module 1 — edge authz tests (inactive tenant + SaaS 403)
id: 31
priority: high
status: completed
milestone: 1
module: A-core
area: tests
effort: S
dependencies: 29
bug: none
created: 2026-11-03
updated: 2026-11-03
---

# Module 1 — edge authz tests (inactive tenant + SaaS 403)

## What to do (What)
Add automated tests for Module_1 §18 gaps that item 28 did not fully cover:

1. Deactivated tenant: staff cannot use the API / login as designed (middleware or auth)
2. Tenant staff cannot access `/api/saas/*` (403)

## Current / Before
- Item 28: 38 passed including isolation
- `test_auth.py` covers inactive **staff**, not clearly deactivated **tenant** host
- `test_saas_registry.py` covers SaaS admin paths; no staff-vs-SaaS 403 case

## Expected / After
- New tests green with existing pytest command path
- Behaviour matches middleware/auth (fix app only if test proves bug; use triage-before-fix)

## Why
Module 1 DoD: deactivated complex blocked; Super Admin scope separate from tenant Owner/staff.

## Acceptance Criteria (complete only when all ticked)
- [ ] Deactivated-tenant denial test exists and passes
- [ ] Non-SaaS staff → `/api/saas/*` denied (403)
- [ ] Suite still green (or failures triaged)
- [ ] `work/changes/YYYY-MM-DD--31-<desc>.md` written
- [ ] PLAN.md + SUMMARY.md updated

## Files Likely Affected
- `backend/tests/api/test_auth.py`
- `backend/tests/api/test_saas_registry.py`
- Possibly `backend/tenancy/middleware.py` / auth if bug found

## Notes / Follow-ups
- Testing-agent style: prefer writing tests; run only if user allows (default coding-agent is code-only — user may override to run)
- No Docker/setup work

---

## Return to Pending Log (when work stops midway)

(none yet)

---

## Completion Log (when work is complete)

- **Completed by**: OpenCode agent
- **Date**: 2026-11-03
- **What was done**: Added 2 edge authz tests to test_auth.py: (1) deactivated tenant host → login 404 via TenantResolveMiddleware; (2) staff session → /api/saas/auth/me/ → 403 (SaaS scope separated from tenant staff). Full suite: 40 passed (38 + 2 new).
- **Files changed**: `backend/tests/api/test_auth.py`
- **Verification**: `pytest -q` → 40 passed, 0 failed
- **Change report**: `work/changes/2026-11-03--31-m1-edge-authz-tests.md`
- **PLAN / SUMMARY**: updated yes
