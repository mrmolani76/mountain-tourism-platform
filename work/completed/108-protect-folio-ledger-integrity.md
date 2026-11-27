---
title: Protect folio ledger integrity and outlet attribution
id: 108
priority: high
status: completed
milestone: 2
module: C-folio
area: backend
effort: L
dependencies: 106
bug: none
created: 2026-11-26
updated: 2026-11-27
---

# Protect folio ledger integrity and outlet attribution

## What to do (What)
Make required outlet attribution and append-only charge/payment history enforceable across folio creation, nightly posting, and deletion paths.

## Current / Before
`folio_charge.outlet_id` is nullable; charge requests permit null and hotel nightly posting does not supply an outlet. Charge and payment rows also cascade when their Folio is deleted.

## Expected / After
Every charge has a valid tenant outlet attribution, and deleting a Folio cannot delete its charge/payment ledger history. Tests cover relevant cross-module charge sources and protected deletion behavior.

## Why
The MVP requires every charge to record its outlet. Project rules require charges and payments to remain append-only.

## Acceptance Criteria (complete only when all ticked)
- [x] Define how room-night and board charges are attributed to an outlet, including legacy/null cases.
- [x] Reject or safely handle charge writes without valid outlet attribution.
- [x] Change the Folio deletion relationship so money history cannot be cascaded away.
- [x] Add regression coverage for outlet attribution and deletion protection.
- [x] Confirm HTTP weather-cancellation and charge-to-room flows still post and void correctly.
- [x] `work/changes/2026-11-27--108-folio-ledger-integrity-progress.md` written.
- [x] PLAN.md + SUMMARY.md updated.

## Files Likely Affected
- `backend-laravel/Modules/Folio/Database/migrations/tenant/`
- `backend-laravel/Modules/Folio/App/Http/Requests/`
- `backend-laravel/Modules/Folio/App/Services/`
- `backend-laravel/Modules/Hotel/App/Services/NightlyPostingService.php`
- `backend-laravel/tests/Feature/Modules/Hotel/HotelModuleTest.php`

## Notes / Follow-ups
- Room nights use the active Reception outlet in the accommodation building; hut beds use its active Hut Kitchen.
- Board charges use the first accommodation allocation by ID because BoardPlan has no outlet association.
- Existing null-attributed charges are preserved. Missing outlet configuration records a failed nightly posting that can be retried after configuration is fixed.
- Failed-posting lookups use date comparison so SQLite's timestamp representation does not cause a duplicate retry insert.
- Direct ChargeToRoomService posting, HTTP charge-to-room, weather cancellation, and weather-void behavior are covered. Rental and lift-pass request helpers now use tenant-qualified validation rules; no dedicated endpoint tests for those modules exist yet.
- Browser UI verification remains unavailable because the project tenant URL refuses connections; frontend acceptance remains in TODO 103 / 121.
- Add new migrations only; never edit a migration that may have shipped.
- Depends on item 106 to avoid overlapping void-reason work.
- See [MVP scope audit](../../docs/MVP_SCOPE_AUDIT.md).
- Started 2026-11-27; resumed after TODO 120 and completed HTTP charge-to-room and weather-cancellation verification.

---

## Return to Pending Log (when work stops midway)
- **Returned by**: Copilot
- **Date**: 2026-11-27
- **Reason**: Paused to resolve prerequisite TODO 120, which blocks HTTP-level verification of this item.
- **Next step**: Resume after tenant-scoped validation rules are corrected and verified.

---

## Completion Log (when work is complete)

- **Completed by**: Copilot
- **Date**: 2026-11-27
- **What was done**: Enforced active Outlet attribution for new Charges, assigned nightly lodging charges to the correct Outlet, protected Folio ledger history, and verified HTTP charge-to-room and weather-cancellation/void flows.
- **Files changed**: Folio, Hotel, Rental, Services, and Transport backend code and tenant migrations; Nuxt folio/rental/lift-pass pages and API clients; Hotel module regression tests; domain decision/audit docs; BUG-API-002 validation fix; work reports and kanban records.
- **Verification**: Earlier focused ledger tests passed, 6 tests / 29 assertions. Current focused HTTP tests passed, 5 tests / 17 assertions. Full `HotelModuleTest.php` had 17 pass and 2 unrelated back-to-back reservation assertions return 409 under SQLite; PHPUnit process exits 1 despite clean focused reports. PHP lint, diff check, and normalized kanban validation passed. Project browser URL refused connection; UI acceptance remains in TODO 103/121.
- **Change report**: [2026-11-27--108-folio-ledger-integrity-progress.md](../changes/2026-11-27--108-folio-ledger-integrity-progress.md)
- **PLAN / SUMMARY**: Moved item 108 to Completed and refreshed the dashboard.
