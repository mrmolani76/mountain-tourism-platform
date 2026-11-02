---
title: Auth API — login, roles, outlet access, password reset
id: 05
priority: high
status: todo
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
