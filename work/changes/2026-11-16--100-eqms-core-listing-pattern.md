# Change Report: Align Core listings to EQMS list/config pattern

- **Date**: 2026-11-16
- **By**: Agent
- **Change ID**: 100

## 1. Summary

Core DataTable listings now follow eQMS Complere shape: flat `listing.php`, shared `ListingConfigService` envelope, and `POST /api/datatableListingConfig`.

## 2. Before

- buildings/seasons/outlets used nested `headers` + `model` inside listing.php
- No `datatableListingConfig` route (SPA 404 on column holder fetch)

## 3. After

- Flat EQMS listing keys (`fields`, `default_order`, `default_sort`)
- Model meta in `CoreListingController::RESOURCES`
- `ListingConfigService::build/table` shared by Staff + Core CRUD listings
- `POST datatableListingConfig` returns dropdown options

## 4. Reason

User: implement the same EQMS listing pattern.

## 5. Files Affected

- `Modules/Core/config/listing.php`
- `Modules/Core/App/Services/ListingConfigService.php` (new)
- `Modules/Core/App/Http/Controllers/CoreListingController.php`
- `Modules/Core/App/Http/Controllers/DatatableListingConfigController.php` (new)
- `Modules/Core/App/Http/Controllers/UserListingController.php`
- `Modules/Core/routes/api.php`
- `frontend/composables/api-clients/useSharedApiClient.ts`
- `frontend/components/data-table/DataTable.vue`
- work kanban / change report

## 6. Verification / Status

- [ ] Code-only (no run)
- [ ] Browser: Core → Buildings/Seasons/Outlets/Staff list/config 200
