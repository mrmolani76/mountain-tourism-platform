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

**Todo list design**: [`work/phases/01-todos/A-core-and-super-admin.md`](../phases/01-todos/A-core-and-super-admin.md) → TODO-06

## What to do (What)
Owner APIs for settings, buildings (altitude + map position), outlets, seasons, staff CRUD + outlet access. All tenant-scoped.

## Current / Before
No domain CRUD beyond auth/tenant.

## Expected / After
Full Module A configuration APIs + cross-tenant denial tests.

## Why
PDF Milestone 1 / §5A core entities.

## Acceptance Criteria (complete only when all ticked)
- [ ] Settings GET/PATCH (currency, tax, timezone, units)
- [ ] Building CRUD with altitude + map position
- [ ] Outlet CRUD under buildings
- [ ] Season CRUD with kind + dates
- [ ] Staff CRUD + outlet access assign
- [ ] Cross-tenant tests on resources
- [ ] OpenAPI updated
- [ ] Change report + PLAN/SUMMARY updated

## Files Likely Affected
- `backend/` core domain apps + tests
