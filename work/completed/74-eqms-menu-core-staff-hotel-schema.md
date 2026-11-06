---
title: EQMS menu + Core staff CRUD wire + Hotel schema relations
id: 74
priority: high
status: completed
milestone: 2
module: A-core
area: frontend
effort: L
dependencies: 71,72
bug: none
created: 2026-11-06
updated: 2026-11-06
---

# EQMS menu + Core staff CRUD + Hotel schema

## What to do (What)

1. Port eQMS Complere menu pattern: backend `config/menu.php` + `GET /api/core/menus`, frontend layout fetches menu (not static `appNavigation`).
2. Menu shows **Core + Hotel only** for now (no folio/pos/rental/etc. nav).
3. Audit Core staff (user) CRUD: backend gaps + wire frontend `/users` pages to Laravel `/api/staff`.
4. Confirm Hotel DB schema + relations vs PDF §5.B; add ER diagram and fill any missing Eloquent relations.

## Current / Before

- Frontend menu is static in `frontend/utils/app-modules.ts` (`appNavigation`).
- Layout uses static `appNavigation`; eQMS uses `GET core/menus` via `useCoreService.getVerticalMenuData`.
- Backend `StaffController` has full index/store/show/update/destroy (owner-only).
- Frontend `pages/users/*` still call eQMS paths (`/users/list`, `/user`) which do not exist on Laravel.
- Hotel tenant tables + models exist under `Modules/Hotel`; `docs/DATABASE_SCHEMA.md` §5 documents them but has no ER diagram.

## Expected / After

- `Modules/Core/config/menu.php` + `MenuController` / service + route `GET core/menus`.
- Frontend loads menu from API; only Core + Hotel entries.
- Staff list/create/edit/delete UI talks to `/api/staff`.
- Hotel ER + relations documented; model relations complete where missing.

## Why

User asked to match eQMS menu flow, keep only Core + Hotel modules visible, finish staff CRUD wiring, and lock Hotel schema/relations to the MVP PDF.

## Acceptance Criteria (complete only when all ticked)

- [x] Backend menu config + `GET /api/core/menus` (role-filtered, Core + Hotel only)
- [x] Frontend layout fetches menu from API (eQMS pattern)
- [x] Staff CRUD frontend wired to Laravel `/users/*` listing + `/staff` resource
- [x] Hotel schema ER + relation notes in `docs/DATABASE_SCHEMA.md`; model relations filled if gaps
- [x] Tests N/A (code-only; user said no setup)
- [x] `work/changes/2026-11-06--74-eqms-menu-core-staff-hotel-datatable.md` written
- [x] PLAN.md + SUMMARY.md updated

## Files Likely Affected

- `backend-laravel/Modules/Core/config/menu.php`
- `backend-laravel/Modules/Core/App/Http/Controllers/MenuController.php`
- `backend-laravel/Modules/Core/App/Services/MenuService.php`
- `backend-laravel/Modules/Core/routes/api.php`
- `frontend/layouts/components/DefaultLayoutWithVerticalNav.vue`
- `frontend/composables/core/useCoreService.ts`
- `frontend/utils/app-modules.ts`
- `frontend/pages/users/*`
- `frontend/composables/api-clients/useUserApiClient.ts`
- `docs/DATABASE_SCHEMA.md`
- `backend-laravel/Modules/Hotel/App/Models/*`
- `frontend/utils/listing-table.ts`
- `frontend/components/core/ResourceCrudPage.vue`
- `frontend/components/hotel/HotelCrudPage.vue`
- `frontend/pages/hotel/availability.vue`

## Notes / Follow-ups

- Code only: no Docker, migrate, or test runs.
- Folio backend may remain (Hotel depends on it) but must not appear in the MVP menu.
- Pinned-items / websocket live menu refresh from eQMS are out of scope for this slice.
- Follow-on: Hotel inventory pages already use `HotelListingPage` DataTable; availability + Resource/Hotel CRUD also DataTable.

---

## Return to Pending Log (when work stops midway)

## Completion Log (when work is complete)

- **Completed by**: Agent
- **Date**: 2026-11-06
- **What was done**: Core+Hotel menu API + layout fetch; staff DataTable listing via `/users/*`; Hotel view tabs/links; Hotel listing APIs; Spatie Data DTOs for Hotel; all remaining product `VDataTable` lists moved to EQMS `DataTable` (availability, ResourceCrudPage, HotelCrudPage) via `listing-table.ts` helpers.
- **Files changed**: See change report. Latest uncommitted: `listing-table.ts`, `availability.vue`, `ResourceCrudPage.vue`, `HotelCrudPage.vue`.
- **Verification**: Grep: no `VDataTable` under `frontend/pages` or `frontend/components/hotel`. Code-only; no browser/test run.
- **Change report**: `work/changes/2026-11-06--74-eqms-menu-core-staff-hotel-datatable.md`
- **PLAN / SUMMARY**: Updated on complete.
