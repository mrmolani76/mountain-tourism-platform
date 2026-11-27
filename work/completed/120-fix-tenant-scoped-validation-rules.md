---
title: Fix tenant-scoped validation rule builders
id: 120
priority: high
status: completed
milestone: 1
module: A-core
area: backend
effort: L
dependencies: none
bug: BUG-API-002
created: 2026-11-27
updated: 2026-11-27
---

# Fix Tenant-scoped Validation Rule Builders

## What to do (What)
Replace unsupported `Rule::exists(...)->connection(...)` and `Rule::unique(...)->connection(...)` calls with Laravel-supported validation rules that target the active tenant database.

## Current / Before
- Runtime requests fail with `Call to undefined method Illuminate\Validation\Rules\Exists::connection()`.
- The failing helper calls are shared across Hotel, Folio, Pos, Rental, Services, Transport, Weather, and Reports.
- Hotel reservation and Folio charge validation both produced HTTP 500 during item 106 test setup.

## Expected / After
Tenant-owned `exists` and `unique` validation checks run against the active tenant connection and return ordinary validation responses rather than HTTP 500 errors.

## Why
The current helper API is unsupported by the installed Laravel version and breaks request validation across multiple MVP modules.

## Acceptance Criteria (complete only when all ticked)
- [x] Replace unsupported connection-builder calls across all affected modules without weakening tenant scoping.
- [x] Add focused tests for representative `exists` and `unique` rules, including a tenant isolation check.
- [x] Verify affected API requests return expected success or validation responses, not HTTP 500.
- [x] `work/changes/2026-11-27--120-tenant-validation-rules.md` written.
- [x] PLAN.md + SUMMARY.md updated.

## Files Likely Affected
- `backend-laravel/Modules/Hotel/`
- `backend-laravel/Modules/Folio/`
- `backend-laravel/Modules/Pos/`
- `backend-laravel/Modules/Rental/`
- `backend-laravel/Modules/Services/`
- `backend-laravel/Modules/Transport/`
- `backend-laravel/Modules/Weather/`
- `backend-laravel/Modules/Reports/`
- `backend-laravel/tests/Feature/`

## Notes / Follow-ups
- Confirm exact framework-supported syntax against the installed Laravel version before editing.
- This is a separate API bug discovered during item 106 regression verification; no production fix is included in item 106.

---

## Return to Pending Log (when work stops midway)

---

## Completion Log (when work is complete)

- **Completed by**: Copilot
- **Date**: 2026-11-27
- **What was done**: Replaced unsupported tenant validation rule-builder calls with Laravel's connection-qualified table syntax, and covered active-tenant scope plus HTTP `exists` and `unique` flows.
- **Files changed**: Hotel, Folio, Pos, Rental, Reports, Services, Transport, and Weather validation helpers; Hotel seasonal-price validation rules; `HotelModuleTest.php`; this work item; BUG-API-002; PLAN, SUMMARY, and MEMORY.
- **Verification**: Focused API flow tests passed, 5 tests / 17 assertions. PHP syntax checks and `git diff --check` passed. Full `HotelModuleTest.php` had 17 passing tests and 2 back-to-back reservation assertions returning 409 under the SQLite test harness; these did not produce validation HTTP 500s and were not changed.
- **Change report**: [2026-11-27--120-tenant-validation-rules.md](../changes/2026-11-27--120-tenant-validation-rules.md)
- **PLAN / SUMMARY**: Moved item 120 to Completed and refreshed the dashboard.
