---
title: Super admin APIs — complex + owner activate/deactivate
id: 07
priority: high
status: todo
milestone: 1
module: A-core
area: backend
effort: M
dependencies: 05
bug: none
created: 2026-11-02
updated: 2026-11-02
---

# Super admin APIs — complex + owner activate/deactivate

**Todo list design**: [`work/phases/01-todos/A-core-and-super-admin.md`](../phases/01-todos/A-core-and-super-admin.md) → TODO-07

## What to do (What)
Super-admin-only APIs: create tenant + owner atomically; activate/deactivate; 403 for others.

## Current / Before
No super admin surface.

## Expected / After
Provision + toggle APIs with tests.

## Why
PDF §5A super admin page (API first).

## Acceptance Criteria (complete only when all ticked)
- [ ] Create complex + owner (atomic) + default settings
- [ ] Activate / deactivate
- [ ] Inactive tenant blocks staff API use
- [ ] Non-super → 403
- [ ] Tests green
- [ ] OpenAPI updated
- [ ] Change report + PLAN/SUMMARY updated

## Files Likely Affected
- `backend/` superadmin endpoints + tests
