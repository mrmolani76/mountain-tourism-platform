---
id: BUG-APP-009
title: Owner dashboard today report returns HTTP 500
status: VERIFIED
type: APP
severity: medium
created: 2026-11-27
---

# BUG-APP-009: Owner dashboard today report returns HTTP 500

## 1. Summary
The owner dashboard cannot load its today summary because `/api/reports/today` returns HTTP 500.

## 2. Environment
Local Laravel + Nuxt application, tenant `lab_local`.

## 3. Steps to Reproduce
1. Sign in to the local tenant as an owner.
2. Open the dashboard at `/`.
3. Wait for the Today figures to load.

## 4. Expected
The dashboard displays today's summary figures or a valid empty state.

## 5. Actual
Before the fix, the dashboard displayed “Today figures could not load. Desk shortcuts below still work.” After the fix, refreshing the dashboard loads the today summary and removes the failure message.

## 6. Evidence
- `frontend/pages/index.vue` requests `/api/reports/today` and displays the failure message.
- Browser check on 2026-11-27 showed the dashboard fallback.
- Laravel request log recorded `/api/reports/today` returning HTTP 500 at 10:09:04 and 10:11:38.
- After the fix, browser refresh showed open folios, revenue, occupancy, and rentals metrics without the error banner.
- With no rooms configured, occupancy displays `—` and “No rooms configured”.

## 7. Root Cause
`DashboardService::currency()` calls `TenantSettings::on($conn)->query()->first()`. `on()` returns an Eloquent Builder, which has no `query()` method. The captured endpoint response reports `Call to undefined method Illuminate\Database\Eloquent\Builder::query()` at this line.

## Fix Progress
- Reproduced the dashboard fallback and repeated HTTP 500 responses.
- Identified the invalid Builder method call in `DashboardService::currency()`.
- Replaced the invalid `query()` call with `first()` on the tenant-scoped Eloquent Builder.
- Added a focused API regression test. It was authored but not run under the code-only workflow.
- Verified the dashboard response and UI in the browser; the endpoint no longer returns the observed 500.

## 8. Workaround
Dashboard desk shortcuts remain available; use the relevant module pages for current details.

## 9. Suggested Fix
The fix is applied and browser-verified. Automated test execution remains pending.

## 10. Impact
Resolved. Owners can view the dashboard's consolidated daily figures; the no-room state no longer reports false 0% occupancy.

## 11. Related
- Work item: `work/completed/123-fix-owner-dashboard-today-summary.md`
- Browser run: `work/inprogress/103-manual-browser-acceptance-checks.md`
- Other bugs: none
