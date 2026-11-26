# Change Report: Staff listing headers

- **Date**: 2026-11-26
- **By**: Copilot
- **Change ID**: 104

## 1. Summary
Aligned the Staff listing configuration with the Nexro `default_order` pattern and restored the Vuetify header row.

## 2. Before
- `backend-laravel/Modules/Core/config/listing.php` used verbose Staff field definitions and both `default_order` and `default_sort`.
- `ListingConfigService` synthesized an object-shaped `default_sort` from `default_order`.
- Vuetify received that object where its `sortBy` model expects an array. The browser showed an empty header row and `find is not a function`.

## 3. After
- Staff fields use the Nexro-style flat field entries and `default_order`.
- `ListingConfigService` preserves server sort metadata and omits `default_sort` from the DataTable response.
- `DataTableHeaderConfig` now reflects optional `default_order` and array-shaped `default_sort`.

## 4. Reason
The Staff table headers were not rendering because the API sent an object as Vuetify's `sortBy` value. The fix follows Nexro's server-side `default_order` pattern without adding new table behavior.

## 5. Files Affected
- `backend-laravel/Modules/Core/config/listing.php`
- `backend-laravel/Modules/Core/App/Services/ListingConfigService.php`
- `frontend/types/data-table-config.ts`
- `work/bugs/BUG-APP-007-staff-listing-table-header-missing.md`
- `work/bugs/INDEX.md`
- `work/PLAN.md`
- `work/SUMMARY.md`
- `work/MEMORY.md`
- `work/completed/104-staff-listing-table-header-fix.md`

## 6. Verification / Status
- [x] Browser verified Staff headers Email, Role, Status, Actions with eight rows.
- [x] Browser verified Buildings, Outlets, and Seasons headers.
- [ ] Unit/build checks not run, coding-agent mode is code-only unless explicitly overridden.
- [x] No data-changing browser actions were performed.
