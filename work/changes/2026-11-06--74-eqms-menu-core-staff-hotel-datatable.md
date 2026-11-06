# Change Report: Item 74 — EQMS menu, staff CRUD, Hotel DataTable

- **Date**: 2026-11-06
- **By**: Agent
- **Change ID**: 74

## 1. Summary

Closed work 74: Core+Hotel menu from API, staff listing wired to Laravel `/users/*`, Hotel schema relation map already in docs, and all Hotel/Core list screens now use EQMS `DataTable` (no bare `VDataTable` on product pages).

## 2. Before

- Vertical nav could fall back to static `appNavigation`; Hotel inventory pages mixed custom `VDataTable` with listing APIs.
- `ResourceCrudPage` and `HotelCrudPage` rendered rows with raw `VDataTable`.
- `pages/hotel/availability.vue` used two `VDataTable` blocks for rooms and hut beds.
- No shared helper to build DataTable config/cells from simple `HotelColumn` defs.

## 3. After

- Menu: `GET /api/core/menus` + frontend `useCoreService.getVerticalMenuData` in default layout (Core + Hotel only).
- Staff: Nuxt `/users` uses listing config/list create/destroy activate paths under `/users/*` on Laravel Core.
- Hotel lists: `HotelListingPage` + listing config/list APIs; buildings/seasons via `ResourceCrudPage` + `DataTable`.
- Shared helpers: `frontend/utils/listing-table.ts` (`buildDataTableConfig`, `mapRowToDataTableCells`, optional `includeActions: false`).
- `availability.vue`, `ResourceCrudPage.vue`, `HotelCrudPage.vue` all use `DataTable` with cell-shaped rows.
- Hotel relation map remains in `docs/DATABASE_SCHEMA.md` §5.0.

## 4. Reason

User asked EQMS-style menu/staff/Hotel wiring, then listing views with links, then **DataTable use karo** everywhere lists still used `VDataTable`.

## 5. Files Affected

- `frontend/utils/listing-table.ts` (new)
- `frontend/pages/hotel/availability.vue`
- `frontend/components/core/ResourceCrudPage.vue`
- `frontend/components/hotel/HotelCrudPage.vue`
- Prior 74 commits (menu, staff listing, Hotel listing/views, Spatie Data DTOs, AppTabs) already on branch
- `work/completed/71-laravel-modules-port.md` (frontmatter `status` repaired to `completed` for kanban)

## 6. Verification / Status

- [x] Product pages under `frontend/pages` and `frontend/components/hotel` have no `VDataTable` (only `DataTable` / `DataTableServer` inside shared DataTable component)
- [ ] Runtime browser check not run (code-only default)
- [ ] Migrations/Scripts pending? No
- [ ] Notes: Code-only; no Docker/test run
