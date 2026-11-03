---
title: Module 1 verification — run DB-per-tenant suite, fix failures
id: 28
priority: high
status: completed
milestone: 1
module: A-core
area: tests
effort: L
dependencies: 27
bug: none
created: 2026-11-03
updated: 2026-11-03
---

# Module 1 verification — run DB-per-tenant suite, fix failures

## What to do (What)
Module 1 (Core & Super Admin) deep analysis (`work/phases/Module_1_Core_Super_Admin_Deep_Analysis.md`) says: do not rebuild; verify. Execute the verification pass that was never completed:

1. Read existing tenancy/auth/core/saas code (done as part of this item)
2. Run the full backend test suite (`pytest`, testpaths=tests) inside the dev container — never run since DB-per-tenant rewrite (SUMMARY §3)
3. Triage failures with `triage-before-fix`; fix code (app or tests) only with evidence; never weaken tests to pass
4. Confirm tenant-isolation acceptance test exists and passes; if missing, add it
5. Report Module 1 status vs Definition of Done (Phase doc §20)

## Current / Before
- Tests written for DB-per-tenant; suite never executed (SUMMARY §3: "tests rewritten … not run")
- Isolation test code exists (`backend/tests/integration/`) but unverified
- Audit 2026-11-03: Module A strongest, but automated isolation unproven; domain modules have a separate critical auth defect (out of scope for this item)

## Expected / After
- `pytest` runs green (or every failure triaged and fixed with evidence)
- Isolation acceptance test proves two tenants cannot see each other's data
- Module 1 verification report in this item + change report

## Why
Client acceptance: two complexes on one server cannot see each other's data — must be proven by automated test, not manual checks. Module 1 gates all other modules.

## Acceptance Criteria (complete only when all ticked)
- [x] Full `pytest` suite executed in dev container; results recorded
- [x] Every failure triaged (triage-before-fix) and resolved or documented with evidence
- [x] Tenant isolation acceptance test runs and passes
- [x] Module 1 status vs Definition of Done documented
- [x] Change report written; PLAN + SUMMARY updated

## Files Likely Affected
- `backend/tests/**` (only if test bugs found)
- `backend/tenancy/**`, `backend/modules/**` (only if code bugs found)

## Notes / Follow-ups
- Domain modules (hotel…reports) auth defect = separate bug + fix item (not this one)
- Client decisions (Phase doc §19, 19 questions) = listed for user; not decided here
- Frontend screens deferred (user opens frontend explicitly)
- No setup/infra work unless user asks

---

## Return to Pending Log (when work stops midway)

None.

---

## Completion Log (when work is complete)

- **Completed by**: OpenCode agent
- **Date**: 2026-11-03
- **What was done**: First full run of the DB-per-tenant suite (never executed after the rewrite). Created + migrated scratch DBs `lab_test`/`other_test`. Fixed 3 app bugs (BUG-APP-002 pagination 500 on all list endpoints; BUG-APP-003 season cross-kind overlap; BUG-APP-004 login 401), 3 test-infra faults (conftest central-DB pointer + missing backend defaults; tenant aliases now explicit via `id=1/2`; ALLOWED_HOSTS overrides) and test bugs (envelope reads, tenant context around direct ORM). `databases='__all__'` rejected (stale class snapshot in Django 5.2). Final: **38 passed**, isolation acceptance test green.
- **Files changed**: `backend/common/pagination/__init__.py`, `backend/modules/core/services/config.py`, `backend/modules/core/auth.py`, `backend/modules/core/views/api.py`, `tests/conftest.py`, `tests/api/test_auth.py`, `tests/api/test_core_config.py`, `tests/integration/test_tenant_db_isolation.py`
- **Verification**: `pytest -q` → 38 passed (0 failed); isolation test asserts tenant A never sees tenant B staff; front desk 403; unknown host 404
- **Change report**: `work/changes/2026-11-03--28-module1-test-run-fixes.md`
- **PLAN / SUMMARY**: updated yes
