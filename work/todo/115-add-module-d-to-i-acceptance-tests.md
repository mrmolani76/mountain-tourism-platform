---
title: Add automated acceptance tests for Modules D through I
id: 115
priority: high
status: todo
milestone: 5
module: docs
area: tests
effort: L
dependencies: none
bug: none
created: 2026-11-26
updated: 2026-11-26
---

# Add automated acceptance tests for Modules D through I

## What to do (What)
Add backend acceptance coverage for the module completion checks that currently have no module feature test files.

## Current / Before
The existing Laravel suite includes relevant SaaS/Core/Hotel coverage. No module feature tests were found for POS, Rental, Services, Transport, Weather or Reports.

## Expected / After
Tests cover each module's MVP acceptance-critical behavior and prove cross-module folio effects and tenant isolation where relevant.

## Why
Work-item completion labels and code presence do not prove the PDF's module “done when” requirements.

## Acceptance Criteria (complete only when all ticked)
- [ ] POS tests cover board-included meals, paid extras and folio charging.
- [ ] Rental tests cover no double-rent, return timing, late fees and damage charges.
- [ ] Services tests cover capacity, staff conflicts, cancellation/charge behavior and outing headcount.
- [ ] Transport tests cover driver/vehicle overlap, seat capacity, reservation pickups/drop-offs and lift status.
- [ ] Weather tests cover forecast-to-warning evaluation, cancellation, correct folio voids and lobby propagation.
- [ ] Reports tests reconcile date-range totals against known charges/payments and tenant-local date boundaries.
- [ ] Run the focused tests and full approved backend suite; record real results.
- [ ] `work/changes/YYYY-MM-DD--115-<desc>.md` written.
- [ ] PLAN.md + SUMMARY.md updated.

## Files Likely Affected
- `backend-laravel/tests/Feature/Modules/`
- `backend-laravel/Modules/Pos/`
- `backend-laravel/Modules/Rental/`
- `backend-laravel/Modules/Services/`
- `backend-laravel/Modules/Transport/`
- `backend-laravel/Modules/Weather/`
- `backend-laravel/Modules/Reports/`

## Notes / Follow-ups
- If any test fails, classify the failure before changing test or application code.
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
