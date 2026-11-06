# Change Report: Item 75 — EQMS DataTable listing align

- **Date**: 2026-11-06
- **By**: Agent
- **Change ID**: 75

## 1. Summary

Hotel and Core list pages now follow the EQMS pattern: `GET {resource}/list/config` + `POST {resource}/list` (or availability GET with cell-shaped rows). Client-side `buildDataTableConfig` / `mapRowToDataTableCells` removed.

## 2. Before

- Hotel: `GET/POST hotel/listings/{resource}/config|list`
- Buildings / seasons: client-built DataTable config + mapped cells from plain CRUD index
- Availability: client mapped Free/Taken cells
- Unused `HotelCrudPage` + `frontend/utils/listing-table.ts`

## 3. After

- Hotel: `GET hotel/{resource}/list/config` + `POST hotel/{resource}/list` for inventory, season-prices, board-plans, reservations, stays
- Core: `buildings/list/config|list` and `seasons/list/config|list` via `CoreListingController`
- Availability: `GET hotel/availability/list/config` + cell-shaped rows from `GET hotel/availability`
- Frontend: `HotelListingPage`, `ResourceCrudPage`, reservations/stays/availability use server config; `useHotelListingApiClient` added
- Removed: `listing-table.ts`, unused `HotelCrudPage.vue`

## 4. Reason

User asked listings to work like EQMS (server config + server list + DataTable), not client-side fake mapping.

## 5. Files Affected

- `backend-laravel/Modules/Hotel/routes/api.php`
- `backend-laravel/Modules/Hotel/App/Http/Controllers/HotelListingController.php`
- `backend-laravel/Modules/Hotel/App/Http/Controllers/AvailabilityController.php`
- `backend-laravel/Modules/Hotel/config/listing.php`
- `backend-laravel/Modules/Core/routes/api.php`
- `backend-laravel/Modules/Core/config/listing.php`
- `backend-laravel/Modules/Core/App/Http/Controllers/CoreListingController.php` (new)
- `frontend/composables/api-clients/useHotelListingApiClient.ts` (new)
- `frontend/components/hotel/HotelListingPage.vue`
- `frontend/components/core/ResourceCrudPage.vue`
- `frontend/pages/buildings.vue`, `seasons.vue`, `hotel/availability.vue`, `hotel/reservations/index.vue`, `hotel/stays/index.vue`
- `frontend/composables/api-clients/useHotelApiClient.ts`, `frontend/types/hotel.ts`
- Deleted: `frontend/utils/listing-table.ts`, `frontend/components/hotel/HotelCrudPage.vue`

## 6. Verification / Status

- [x] Grep: no `hotel/listings`, no `buildDataTableConfig` / `mapRowToDataTableCells` in app code
- [ ] Runtime browser / API smoke not run (code-only)
- [ ] Migrations pending? No
- [ ] Notes: Dialog create/edit kept for simple Hotel/Core forms (EQMS users use form pages; Hotel forms stay dialog for MVP)
