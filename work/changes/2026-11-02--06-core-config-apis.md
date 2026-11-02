# Change Report: Core configuration APIs (item 06)

- **Date**: 2026-11-02
- **By**: Buffy (coding agent)
- **Change ID**: item 06 + BUG-TENANT-001 report

## 1. Summary
Module A configuration APIs shipped: TenantSettings (GET/PATCH), Building CRUD, Outlet CRUD under buildings, Season CRUD with overlap rule, staff admin tightened to owner role; migration 0005 + cross-tenant tests added.

## 2. Before
`modules/core` had only auth/settings scaffolding: `Staff`, `StaffOutletAccess`, and a stub `Outlet` (name/is_active) with `views.py` exposing login/me/password-reset/staff router. No buildings, seasons, or tenant settings. Staff CRUD allowed any tenant staff.

## 3. After
- Models: `modules/core/models/building.py` (BuildingType, altitude, lat/lng),expanded `outlet.py` (building FK PROTECT, OutletType), `season.py` (winter/summer/closed), `settings.py` (currency, tax_rates JSON, timezone, units).
- Services: `modules/core/services/config.py` — singleton `get_tenant_settings()`, `validate_season_overlap()` (MVP rule: any overlap rejected, documented).
- Serializers: `modules/core/serializers/config.py` — Building/Outlet/Season/TenantSettings; Season overlap validated.
- Views/URLs: `TenantSettingsView` (`/api/settings/` GET+PATCH), router `buildings|outlets|seasons`, ApiRoot extended.
- Permissions: new `IsOwner`; staff + outlet-access viewsets now owner-only.
- Migration: `modules/core/migrations/0005_building_season_settings_outlet_config.py` (nullable FK then AlterField to non-null).
- Tests: `tests/test_core_config.py` (25 tests incl. cross-tenant 404, owner-only 403s); `tests/test_tenant_db_isolation.py` updated for owner-only staff list + front-desk 403 assertion.
- Bug report: `work/bugs/BUG-TENANT-001-cross-host-session-identity.md` (cross-host session identity swap risk, fix path logged).

## 4. Reason
PDF Milestone 1 §5A: owner must configure complex core data via API. TODO-06 in `work/phases/01-todos/A-core-and-super-admin.md`.

## 5. Files Affected
- `backend/modules/core/models/{building,outlet,season,settings,__init__}.py`
- `backend/modules/core/services/{config,__init__}.py`
- `backend/modules/core/serializers/{config,__init__,staff}.py`
- `backend/modules/core/views/{api,__init__}.py`, `backend/modules/core/urls.py`
- `backend/modules/core/permissions/{base,__init__}.py`
- `backend/modules/core/migrations/0005_building_season_settings_outlet_config.py`
- `backend/tests/{test_core_config,test_auth,test_tenant_db_isolation}.py`
- `work/PLAN.md`, `work/SUMMARY.md`, `work/bugs/{INDEX.md,BUG-TENANT-001-*.md}`, `work/inprogress|completed/06-*.md`

## 6. Verification / Status
- [x] `python3 manage.py check` — 0 issues
- [x] `makemigrations core --check --dry-run` — no changes (migration matches models)
- [x] `py_compile` on all touched files — OK
- [ ] Test suite execution — code-only per repo policy (physical tenant DB setup on other machine); BUG-TENANT-001 logged during review
- Notes: OpenAPI `/api/schema/` auto-covers new routers (AutoSchema).
