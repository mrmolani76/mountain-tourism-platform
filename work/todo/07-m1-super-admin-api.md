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

## What to do (What)
Super-admin-only APIs to create a complex (tenant) and its owner account; activate/deactivate complex. No Nuxt super-admin page yet.

## Current / Before
No super admin surface.

## Expected / After
Protected endpoints; only super admin; creates tenant + owner; activate/deactivate flags respected by auth/tenant access.

## Why
PDF §5A / Milestone 1 super admin page (API first).

## Acceptance Criteria (complete only when all ticked)
- [ ] Create complex + owner via API
- [ ] Activate/deactivate works; inactive tenant blocked
- [ ] Non–super-admin gets 403
- [ ] Tests pass
- [ ] Change report + PLAN/SUMMARY updated

## Files Likely Affected
- `backend/` saas/superadmin app, tests

## Notes / Follow-ups
- Super admin UI deferred.
