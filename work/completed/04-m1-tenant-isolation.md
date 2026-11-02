---
title: Tenant isolation layer + cross-tenant test
id: 04
priority: high
status: completed
milestone: 1
module: A-core
area: backend
effort: M
dependencies: 03
bug: none
created: 2026-11-02
updated: 2026-11-02
---

# Tenant isolation layer + cross-tenant test

**Todo list design**: [`work/phases/01-todos/A-core-and-super-admin.md`](../phases/01-todos/A-core-and-super-admin.md) → TODO-04

## What to do (What)
Tenant model + single isolation layer + automated test that two complexes cannot see each other’s data (PDF §5A done when).

## Current / Before
No Tenant / isolation layer.

## Expected / After
All tenant queries go through one mechanism; cross-tenant test green.

## Why
Module A acceptance; PDF §7 isolation in one place.

## Acceptance Criteria (complete only when all ticked)
- [ ] Tenant model with is_active
- [ ] tenant_id on tenant-owned base
- [ ] Single isolation layer implemented
- [ ] Client-sent tenant_id not trusted for authz
- [ ] Automated cross-tenant list+detail test passes
- [ ] Change report + PLAN/SUMMARY updated

## Files Likely Affected
- `backend/` models, managers, tests
- `docs/DECISIONS.md` / ARCHITECTURE if needed

---
## Completion Log (when work is complete)

- **Completed by**: Agent
- **Date**: 2026-11-02
- **What was done**: Implemented tenant isolation layer (TenantManager base model + `tenant_id` FK, TenantContextMiddleware resolving tenant from authenticated staff), Tenant + Staff models, staff/tenant API stubs, migration core.0001/0002, and cross-tenant isolation tests (5 tests, all green: list/deny, detail/deny, own-detail/allow, no-context-empty, health-public). Client-sent tenant_id never trusted; tenant resolved only from request.user.
- **Files changed**: `backend/apps/core/models/{__init__,base,tenant,staff}.py`, `backend/apps/core/middleware.py`, `backend/apps/core/views.py`, `backend/apps/core/serializers.py`, `backend/apps/core/urls.py`, `backend/apps/core/migrations/0001_initial.py`, `backend/apps/core/migrations/0002_staff_last_login.py`, `backend/tests/test_tenant_isolation.py`, `backend/config/settings/base.py`, `backend/config/urls.py`
- **Verification**: `python3 manage.py test tests.test_tenant_isolation` → 5/5 OK; `manage.py check` clean; `migrate` applied.
- **Change report**: `work/changes/2026-11-02--04-m1-tenant-isolation.md`
- **PLAN / SUMMARY**: updated yes
