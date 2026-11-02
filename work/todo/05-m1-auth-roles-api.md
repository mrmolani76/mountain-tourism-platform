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

## What to do (What)
Email/password login API; staff belong to one tenant; roles from PDF §3; outlet access; owners/managers reset staff passwords **without email**. Permissions enforced on API.

## Current / Before
No auth endpoints.

## Expected / After
Login/session-or-token API documented in OpenAPI; role + outlet checks on protected routes; password reset by privileged staff only.

## Why
PDF §5A login and staff access; no email in MVP.

## Acceptance Criteria (complete only when all ticked)
- [ ] Login works for staff scoped to tenant
- [ ] Roles model matches PDF roles needed for Phase 1
- [ ] Outlet access enforced server-side
- [ ] Staff password reset without email sending
- [ ] Tests for 401/403 cases
- [ ] Change report + PLAN/SUMMARY updated

## Files Likely Affected
- `backend/` auth, users, permissions, tests

## Notes / Follow-ups
- UI login screen deferred (API only).
