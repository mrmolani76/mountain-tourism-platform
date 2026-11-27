---
title: Fix generic folio void reason default
id: 106
priority: high
status: completed
milestone: 2
module: C-folio
area: backend
effort: S
dependencies: none
bug: BUG-MONEY-001
created: 2026-11-26
updated: 2026-11-27
---

# Fix generic folio void reason default

## What to do (What)
Correct how generic folio void requests choose `reason_code`. Preserve explicit `weather` codes for weather-driven cancellations and add a focused regression test.

## Current / Before
`VoidRequest` permits a missing `reason_code`, and `FolioService::voidEntry()` uses `FolioVoid::REASON_WEATHER` when it is omitted. See [BUG-MONEY-001](../bugs/BUG-MONEY-001-default-void-reason-weather.md).

## Expected / After
An ordinary void cannot be silently classified as a weather cancellation. Weather cancellations continue to carry the explicit `weather` code.

## Why
Incorrect reason codes corrupt folio audit history and weather-cancellation reporting. This is required by the append-only money and weather-void rules.

## Acceptance Criteria (complete only when all ticked)
- [x] Ordinary voids without a reason code are recorded with the domain-safe non-weather reason `other`.
- [x] Weather cancellation still records reason code `weather`.
- [x] Added regression coverage for both paths; focused PHPUnit output reports 2 tests, 8 assertions, and 0 failures/errors in its JUnit report.
- [x] `work/changes/2026-11-27--106-folio-void-reason.md` written.
- [x] PLAN.md + SUMMARY.md updated.

## Files Likely Affected
- `backend-laravel/Modules/Folio/App/Http/Requests/VoidRequest.php`
- `backend-laravel/Modules/Folio/App/Services/FolioService.php`
- `backend-laravel/tests/Feature/Modules/Hotel/HotelModuleTest.php` or a focused Folio feature test

## Notes / Follow-ups
- Do not change the immutable charge/payment behavior.
- Ordinary voids now default to `other`; callers can still explicitly send `weather`.
- Regression assertions cover the ordinary `other` default and explicit `weather` code. The focused JUnit report has no failures or errors, although the test process returned exit code 1.
- The complete `HotelModuleTest` file reports 3 passed and 8 failed; all 8 failures are HTTP 500s from the separately tracked `BUG-API-002` (`Exists::connection()`).

## Completion Log

- **Completed by**: Copilot
- **Date**: 2026-11-27
- **What was done**: Defaulted generic folio voids to `other`, preserved explicit `weather`, and added focused regression coverage.
- **Files changed**: `backend-laravel/Modules/Folio/App/Models/FolioVoid.php`, `backend-laravel/Modules/Folio/App/Services/FolioService.php`, `backend-laravel/tests/Feature/Modules/Hotel/HotelModuleTest.php`, and the related work/bug tracking files.
- **Verification**: Focused JUnit report: 2 tests, 8 assertions, 0 failures/errors. The complete Hotel feature file exposes 8 unrelated validation failures tracked by BUG-API-002 / TODO 120. Both focused and full-file PHP invocations reported process exit code 1.
- **Change report**: `work/changes/2026-11-27--106-folio-void-reason.md`
- **PLAN / SUMMARY**: Item 106 moved to Completed and BUG-MONEY-001 closed.
