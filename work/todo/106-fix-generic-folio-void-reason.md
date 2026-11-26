---
title: Fix generic folio void reason default
id: 106
priority: high
status: todo
milestone: 2
module: C-folio
area: backend
effort: S
dependencies: none
bug: BUG-MONEY-001
created: 2026-11-26
updated: 2026-11-26
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
- [ ] Ordinary voids without a reason code are rejected or recorded with a domain-safe non-weather reason.
- [ ] Weather cancellation still records reason code `weather`.
- [ ] Add regression coverage for both paths and run the focused test.
- [ ] `work/changes/YYYY-MM-DD--106-<desc>.md` written.
- [ ] PLAN.md + SUMMARY.md updated.

## Files Likely Affected
- `backend-laravel/Modules/Folio/App/Http/Requests/VoidRequest.php`
- `backend-laravel/Modules/Folio/App/Services/FolioService.php`
- `backend-laravel/tests/Feature/Modules/Hotel/HotelModuleTest.php` or a focused Folio feature test

## Notes / Follow-ups
- Do not change the immutable charge/payment behavior.
- No application code has been changed as part of this audit.
