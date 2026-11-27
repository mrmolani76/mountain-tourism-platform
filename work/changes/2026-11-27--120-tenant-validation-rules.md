# Change Report: Work 120 — Tenant-scoped validation rules

- **Date**: 2026-11-27
- **By**: Copilot
- **Change ID**: 120

## 1. Summary
Replaced unsupported Laravel validation rule connection methods with tenant-qualified table names.

## 2. Before
Validation helpers in Hotel, Folio, Pos, Rental, Services, Transport, Weather, and Reports called `->connection()` on Laravel `Exists` rules. Hotel room-season pricing used the same unsupported method on `Unique`, causing affected HTTP requests to fail with 500 errors.

## 3. After
Tenant `exists` and `unique` rules now use the framework-supported `connection.table` table syntax. Added coverage for active tenant connection isolation, room-season-price uniqueness over HTTP, weather cancellation, and charge-to-room posting/void behavior.

## 4. Reason
The API error blocked ordinary request validation and the remaining Folio endpoint checks in item 108.

## 5. Files Affected
- `backend-laravel/Modules/Hotel/App/Data/HotelData.php`
- `backend-laravel/Modules/Hotel/App/Data/StoreRoomSeasonPriceData.php`
- `backend-laravel/Modules/Hotel/App/Data/UpdateRoomSeasonPriceData.php`
- `backend-laravel/Modules/Folio/App/Http/Requests/FolioRequest.php`
- `backend-laravel/Modules/Pos/App/Http/Requests/PosRequest.php`
- `backend-laravel/Modules/Rental/App/Http/Requests/RentalRequest.php`
- `backend-laravel/Modules/Reports/App/Data/ReportsData.php`
- `backend-laravel/Modules/Services/App/Http/Requests/ServicesRequest.php`
- `backend-laravel/Modules/Transport/App/Http/Requests/TransportRequest.php`
- `backend-laravel/Modules/Weather/App/Data/WeatherData.php`
- `backend-laravel/tests/Feature/Modules/Hotel/HotelModuleTest.php`
- `work/bugs/BUG-API-002-tenant-validation-connection-method.md`
- `work/bugs/INDEX.md`
- `work/completed/120-fix-tenant-scoped-validation-rules.md`
- `work/PLAN.md`
- `work/SUMMARY.md`
- `work/MEMORY.md`

## 6. Verification / Status
- [x] Focused HTTP/API tests: 5 passed, 17 assertions.
- [x] PHP syntax checks passed for all changed PHP files.
- [x] No unsupported validation builder `->connection()` calls remain in module code.
- [ ] Full `HotelModuleTest.php`: 17 passed, 2 existing back-to-back reservation assertions returned 409 under the SQLite test harness. These are unrelated to validation and were not changed.
- [ ] PHPUnit process exit code is 1 despite the focused test report showing all selected tests passed.
- [x] BUG-API-002 marked Verified and TODO 120 moved to Completed.
