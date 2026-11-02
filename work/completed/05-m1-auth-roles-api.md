---
title: Auth API — login, roles, outlet access, password reset
id: 05
priority: high
status: completed
milestone: 1
module: A-core
area: backend
effort: M
dependencies: 04
bug: none
created: 2026-11-02
updated: 2026-11-02
---

# Auth API — login, roles, outlet access, password reset

**Todo list design**: [`work/phases/01-todos/A-core-and-super-admin.md`](../phases/01-todos/A-core-and-super-admin.md) → TODO-05

## What to do (What)
Email/password login; PDF roles; outlet access; owner/manager password reset without email; inactive tenant/staff blocked.

## Current / Before
No auth endpoints.

## Expected / After
Login + me + password reset APIs; role/outlet model; tests for 401/403.

## Why
PDF §5A login and staff access.

## Acceptance Criteria (complete only when all ticked)
- [ ] Staff model with role + tenant_id
- [ ] Roles cover PDF §3 set needed
- [ ] Login / me APIs work
- [ ] StaffOutletAccess wired
- [ ] Owner and manager password reset (no email)
- [ ] Inactive tenant/staff denied
- [ ] Authz tests green
- [ ] OpenAPI updated
- [ ] Change report + PLAN/SUMMARY updated

## Files Likely Affected
- `backend/` accounts/auth/permissions/tests

---
## Completion Log (when work is complete)

- **Completed by**: Agent
- **Date**: 2026-11-02
- **What was done**: Implemented Auth API: Staff model with role (PDF §3 roles enum) + tenant_id + last_login, StaffOutletAccess model, minimal Outlet model stub. Auth views: login (email/password → session), me, logout, reset-password (owner/manager only, no email). Custom StaffSessionAuthentication reads staff_id from session. Tenant/inactive staff/tenant denied. Cross-tenant password reset blocked. 11 auth tests + 5 isolation tests all pass (16 total).
- **Files changed**: `backend/apps/core/auth.py` (new), `backend/apps/core/models/staff.py` (role, all_objects), `backend/apps/core/models/outlet.py` (new), `backend/apps/core/models/staff.py` (StaffOutletAccess), `backend/apps/core/models/__init__.py`, `backend/apps/core/views.py` (auth endpoints), `backend/apps/core/serializers.py` (login/me/reset/outlet-access), `backend/apps/core/urls.py` (auth routes), `backend/apps/core/migrations/0003_*.py`, `backend/config/settings/base.py` (StaffSessionAuthentication), `backend/tests/test_auth.py` (new, 11 tests)
- **Verification**: `python3 manage.py test tests.test_auth tests.test_tenant_isolation` → 16/16 OK; `manage.py check` clean; `migrate` applied.
- **Change report**: `work/changes/2026-11-02--05-m1-auth-roles-api.md`
- **PLAN / SUMMARY**: updated yes
