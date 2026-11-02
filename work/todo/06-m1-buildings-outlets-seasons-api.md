---
title: Buildings, outlets, seasons, tenant settings APIs
id: 06
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

# Buildings, outlets, seasons, tenant settings APIs

## What to do (What)
CRUD APIs for Building (type, altitude, map position), Outlet (in building), Season (dates), tenant settings (currency, tax, timezone, units). All tenant-scoped.

## Current / Before
No domain APIs beyond auth/tenant scaffolding.

## Expected / After
Owner/manager-accessible APIs; validation; tests; OpenAPI updated.

## Why
PDF Milestone 1 + §5A core entities; buildings are weather points later.

## Acceptance Criteria (complete only when all ticked)
- [ ] Building/Outlet/Season/settings endpoints work under tenant scope
- [ ] Altitude and map position stored on buildings
- [ ] Settings include currency, tax, timezone, units
- [ ] Tests cover happy path + cross-tenant denial
- [ ] Change report + PLAN/SUMMARY updated

## Files Likely Affected
- `backend/` apps for core domain
- tests, OpenAPI schema

## Notes / Follow-ups
- Admin UI deferred.
