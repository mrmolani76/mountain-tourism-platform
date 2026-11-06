---
title: Align Hotel/Core listings to EQMS DataTable pattern
id: 75
priority: high
status: completed
milestone: 2
module: B-hotel
area: frontend
effort: M
dependencies: 74
bug: none
created: 2026-11-06
updated: 2026-11-06
---

# Align Hotel/Core listings to EQMS DataTable pattern

## What to do (What)

Match eQMS Complere listing flow for Hotel and Core list pages:

1. Routes: `GET {resource}/list/config` + `POST {resource}/list` (not `/listings/{resource}/…`).
2. Server returns DataTable config + cell-shaped rows (`{ label, type, url? }`).
3. Frontend: thin index pages / shared shell like users — load config, then DataTable `@load-items` posts list params.
4. Stop client-side `buildDataTableConfig` / `mapRowToDataTableCells` for CRUD listings (buildings, seasons, Hotel inventory).

## Current / Before

- Hotel uses `GET/POST hotel/listings/{resource}/config|list`.
- `ResourceCrudPage` / `HotelCrudPage` / `availability.vue` build config and cells on the client via `frontend/utils/listing-table.ts`.
- Users page already matches EQMS (`/users/list/config` + `/users/list`).

## Expected / After

- Hotel: `hotel/{resource}/list/config` + `hotel/{resource}/list` for inventory, reservations, stays.
- Core: `buildings/list/config` + `buildings/list`, same for `seasons`.
- Availability: config from API; rows already cell-shaped from availability endpoint (no client cell mapper for CRUD).
- Pages mirror users index: skeleton → `DataTable` v-bind config → reloadListingData.

## Why

User: "eqms me kese kiya hua hai vese krna hai" — DataTable must follow EQMS server-driven listing, not fake client mapping.

## Acceptance Criteria (complete only when all ticked)

- [x] Hotel listing routes use `{resource}/list/config` + `{resource}/list`
- [x] Buildings + seasons have Core listing config/list APIs and ResourceCrudPage uses them
- [x] No `buildDataTableConfig` / `mapRowToDataTableCells` on CRUD listing pages
- [x] Availability uses server config + cell-shaped rows
- [x] Tests N/A (code-only)
- [x] Change report written
- [x] PLAN.md + SUMMARY.md updated

## Files Likely Affected

See change report.

## Notes / Follow-ups

- Keep dialog create/edit for simple Hotel forms (EQMS users use form pages; Hotel forms stay dialog for MVP).
- Code only.

---

## Return to Pending Log (when work stops midway)

## Completion Log (when work is complete)

- **Completed by**: Agent
- **Date**: 2026-11-06
- **What was done**: Switched Hotel/Core listings to EQMS `list/config` + `list` routes; server cell-shaped rows; availability listingConfig + shaped payload; removed client listing-table helpers and unused HotelCrudPage.
- **Files changed**: See `work/changes/2026-11-06--75-eqms-datatable-listing-align.md`
- **Verification**: Grep clean for old paths/helpers; code-only (no browser run)
- **Change report**: `work/changes/2026-11-06--75-eqms-datatable-listing-align.md`
- **PLAN / SUMMARY**: Updated on complete
