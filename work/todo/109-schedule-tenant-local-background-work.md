---
title: Schedule tenant-local nightly and weather jobs
id: 109
priority: high
status: todo
milestone: 4
module: H-weather
area: backend
effort: L
dependencies: none
bug: none
created: 2026-11-26
updated: 2026-11-26
---

# Schedule tenant-local nightly and weather jobs

## What to do (What)
Register the required recurring nightly posting and hourly weather fetching/evaluation, using each tenant's configured local time.

## Current / Before
`hotel:nightly-posting` exists but is not registered with Laravel's scheduler. Weather fetch/evaluate are staff-triggered API actions, with no hourly scheduled job found. Posting and dashboard date selection use the application date instead of the tenant settings timezone.

## Expected / After
Every active tenant gets idempotent nightly posting at its local night boundary and hourly weather refresh/evaluation. Failures are visible, and one tenant's failure does not silently skip other tenants.

## Why
The PDF requires hourly server-side weather caching and nightly posting using the tenant's local time.

## Acceptance Criteria (complete only when all ticked)
- [ ] Read the timezone from the active tenant's settings with explicit behavior when settings are absent or invalid.
- [ ] Register nightly posting and weather fetch/evaluation in the application scheduler.
- [ ] Preserve the nightly posting idempotency guarantee across retries.
- [ ] Record/report failures per tenant and continue processing other tenants.
- [ ] Add tests for tenant timezone boundaries, scheduling commands, and failure handling.
- [ ] `work/changes/YYYY-MM-DD--109-<desc>.md` written.
- [ ] PLAN.md + SUMMARY.md updated.

## Files Likely Affected
- `backend-laravel/routes/console.php`
- `backend-laravel/Modules/Hotel/App/Console/NightlyPostingCommand.php`
- `backend-laravel/Modules/Hotel/App/Services/NightlyPostingService.php`
- `backend-laravel/Modules/Weather/App/Services/WeatherFetchService.php`
- `backend-laravel/Modules/Reports/App/Services/DashboardService.php`
- `backend-laravel/tests/`

## Notes / Follow-ups
- Keep all tenant business queries on the active tenant connection.
- See [MVP scope audit](../../docs/MVP_SCOPE_AUDIT.md).

---

## Return to Pending Log (when work stops midway)

---

## Completion Log (when work is complete)

- **Completed by**:
- **Date**:
- **What was done**:
- **Files changed**:
- **Verification**:
- **Change report**:
- **PLAN / SUMMARY**:
