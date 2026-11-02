---
title: Buildings, outlets, seasons, tenant settings APIs
id: 06
priority: high
status: completed
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
- [x] Settings GET/PATCH (currency, tax, timezone, units)
- [x] Building CRUD with altitude + map position
- [x] Outlet CRUD under buildings
- [x] Season CRUD with kind + dates
- [x] Staff CRUD + outlet access assign
- [x] Cross-tenant tests on resources
- [x] OpenAPI updated (AutoSchema covers new routers on `/api/schema/`)
- [x] Change report + PLAN/SUMMARY updated

## Files Likely Affected
- `backend/` core domain apps + tests

## Completion Log

- **Completed**: 2026-11-02 by Buffy (coding agent)
- **Result**: Owner-only APIs shipped for TenantSettings (GET/PATCH singleton), Building CRUD (altitude + lat/lng for later weather point), Outlet CRUD (building FK + OutletType), Season CRUD (kind + dates with overlap rule: any overlap rejected, documented in `services/config.py`). Staff CRUD + outlet-access endpoints tightened to owner role (`IsOwner` permission).
- **Endpoints**: `/api/settings/`, `/api/buildings/`, `/api/outlets/`, `/api/seasons/`, `/api/staff/`, `/api/outlet-access/` (owner-only), ApiRoot extended.
- **Migration**: `0005_building_season_settings_outlet_config.py` (append-only; verified against model state via `makemigrations --check`).
- **Tests**: `tests/test_core_config.py` (settings/buildings/outlets/seasons, 403 for non-owner, cross-tenant 404) — code-only per repo policy, execution on setup machine. Isolation test updated for owner-only staff list + front-desk 403.
- **Bug found**: BUG-TENANT-001 logged before fixing (cross-host session identity swap risk; workaround browser cookie scoping; separate fix item).
- **Verification**: `manage.py check` 0 issues; migration-vs-model check clean; `py_compile` all touched files.
- **Change report**: [`work/changes/2026-11-02--06-core-config-apis.md`](../changes/2026-11-02--06-core-config-apis.md)
