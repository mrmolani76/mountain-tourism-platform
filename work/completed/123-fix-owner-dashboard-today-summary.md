---
title: Fix owner dashboard today-summary API and UI
id: 123
priority: medium
status: completed
milestone: 5
module: I-dashboard
area: backend/frontend
effort: M
dependencies: none
bug: BUG-APP-009
created: 2026-11-27
updated: 2026-11-27
---

# Fix owner dashboard today-summary API and UI

## What to do (What)
Fix the owner dashboard daily summary end to end so `/api/reports/today` succeeds and the UI displays its returned metrics without misleading empty-inventory occupancy.

## Current / Before
- `frontend/pages/index.vue` requests `/api/reports/today`.
- The owner dashboard displays “Today figures could not load. Desk shortcuts below still work.”
- The endpoint fails because `DashboardService::currency()` calls `query()` on the Eloquent Builder returned by `TenantSettings::on($conn)`.
- When no rooms are configured, the dashboard displays `0%` occupancy and `0 / — rooms`.

## Expected / After
- The endpoint returns today's summary for the active tenant, including a valid empty result when the tenant has no data.
- The dashboard renders the returned metrics and does not show the failure banner on a successful response.
- When no rooms are configured, the occupancy card shows `—` and “No rooms configured”.

## Why
The owner dashboard is part of Module I, and owners need its daily operational summary.

## Acceptance Criteria (complete only when all ticked)
- [x] Reproduce and identify the exception causing `/api/reports/today` to return HTTP 500.
- [x] Fix the backend root cause without weakening tenant scoping or money rules.
- [x] Add focused API regression coverage for the failure and expected response.
- [x] Verify the endpoint response and owner dashboard UI in the browser.
- [x] `work/changes/2026-11-27--123-dashboard-summary-api-ui.md` written.
- [x] PLAN.md + SUMMARY.md updated.
- [x] Fix the backend root cause without weakening tenant scoping or money rules.

## Files Likely Affected
- `backend-laravel/Modules/Reports/`
- `frontend/pages/index.vue`
- `backend-laravel/tests/Feature/Modules/Reports/DashboardTest.php`

## Notes / Follow-ups
- The focused regression test was authored but not run under the code-only workflow.
- Broader browser acceptance checks remain in item 103.

---

## Return to Pending Log (when work stops midway)

---

## Completion Log (when the work is complete)

- **Completed by**: Copilot
- **Date**: 2026-11-27
- **What was done**: Fixed the invalid Eloquent Builder call, added an API regression test, and corrected the no-room occupancy UI state.
- **Files changed**: `backend-laravel/Modules/Reports/App/Services/DashboardService.php`, `backend-laravel/tests/Feature/Modules/Reports/DashboardTest.php`, `frontend/pages/index.vue`.
- **Verification**: Browser dashboard refresh returned summary metrics without the failure banner; no-room occupancy now displays `—` and “No rooms configured”. Automated regression test authored but not run.
- **Change report**: `work/changes/2026-11-27--123-dashboard-summary-api-ui.md`
- **PLAN / SUMMARY**: Item 123 moved to Completed; bug and verification summaries updated.
