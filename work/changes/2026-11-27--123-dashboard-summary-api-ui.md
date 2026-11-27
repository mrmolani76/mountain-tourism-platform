# Change Report: Fix Owner Dashboard Summary API and UI

- **Date**: 2026-11-27
- **By**: Copilot
- **Change ID**: 123-dashboard-summary-api-ui

## 1. Summary
Fixed the dashboard summary HTTP 500 and clarified the UI when the tenant has no configured rooms.

## 2. Before
- `DashboardService::currency()` called `TenantSettings::on($conn)->query()->first()`. `on()` returns an Eloquent Builder, which has no `query()` method.
- `/api/reports/today` returned HTTP 500, and the dashboard showed its failure banner and placeholder metrics.
- The empty-room dashboard state showed `0%` and `0 / — rooms`.

## 3. After
- `DashboardService::currency()` reads the first tenant setting directly from the tenant-scoped Builder.
- The dashboard loads summary metrics without the failure banner.
- When no rooms are configured, occupancy shows `—` and “No rooms configured”.
- Added `backend-laravel/tests/Feature/Modules/Reports/DashboardTest.php` to cover the owner endpoint and tenant currency.

## 4. Reason
BUG-APP-009 prevented owners from viewing their daily dashboard figures. The empty-room occupancy display also implied a measured 0% despite having no room denominator.

## 5. Files Affected
- `backend-laravel/Modules/Reports/App/Services/DashboardService.php`
- `backend-laravel/tests/Feature/Modules/Reports/DashboardTest.php`
- `frontend/pages/index.vue`
- `work/bugs/BUG-APP-009-dashboard-today-report-500.md`
- `work/bugs/INDEX.md`
- `work/completed/123-fix-owner-dashboard-today-summary.md`
- `work/PLAN.md`
- `work/SUMMARY.md`
- `work/MEMORY.md`

## 6. Verification / Status
- [x] Browser dashboard refresh loaded open folios, revenue, occupancy, and rentals metrics without the failure banner.
- [x] Browser UI displayed `—` and “No rooms configured” for occupancy when no rooms were present.
- [x] Added focused API regression coverage.
- [ ] Automated test execution remains pending under the code-only workflow.
- [x] BUG-APP-009 moved to Verified; item 123 moved to Completed.
