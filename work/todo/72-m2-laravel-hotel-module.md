---
title: Module 2 — Hotel, chalets and huts (Laravel) + minimal folio contract
id: 72
priority: high
status: todo
milestone: 2
module: B-hotel
area: backend
effort: L
dependencies: 71
bug: none
created: 2026-11-04
updated: 2026-11-13
---

# Module 2 — Hotel, chalets and huts (Laravel)

## What to do (What)

Port PDF Module B into `backend-laravel/Modules/Hotel` using the Module 1 tenant patterns
(`Modules/Core` folder shape, `BaseModel`, `Envelope`, `StaffAuthz`, tenant connection).

Scope from `work/phases/Module_2_Hotel_Chalets_Huts_Deep_Analysis.md`:

- Room types, rooms in any building, chalets booked like rooms.
- Hut dormitories with individually bookable beds.
- Room season prices (integer minor units, season from Module A).
- Four board plans with per person per night price and meal inclusions.
- Reservations with dates, adults, children, allocation, board plan, deposit, arrival details.
- Availability + calendar reads (building / room / date), overlap safe.
- Check-in opens `Stay` + `Folio`.
- Nightly posting of room night + board charges, idempotent.
- Checkout blocked while folio balance is non-zero.

Because Module C (folio) is not ported yet, this item also ships the **minimal folio
contract slice** needed by Module B (`folio_folio`, `folio_charge`, `folio_payment`,
`folio_void`, balance service). Module 3 will extend it (invoice payload, outlet reports, UI).

## Current / Before

- `backend-laravel/Modules/` has only `Core` (Module A tenant) and `Saas` (central).
- No hotel or folio tables, models, routes or services exist in Laravel.
- Hotel code from the Django era is gone with `backend/`; only docs remain.

## Expected / After

- `backend-laravel/Modules/Hotel/` with:
  - `Database/migrations/tenant/` hotel tables (`hotel_*`).
  - Models: `RoomType`, `Room`, `HutDormitory`, `HutBed`, `RoomSeasonPrice`, `BoardPlan`,
    `Reservation`, `ReservationAccommodation`, `Stay`, `NightlyPosting`.
  - Services: `AvailabilityService`, `PricingService`, `ReservationService`,
    `CheckInService`, `NightlyPostingService`, `CheckoutService`.
  - Controllers + Form Requests + routes under `api/hotel/*`.
  - Console command `hotel:nightly-posting`.
- `backend-laravel/Modules/Folio/` minimal slice: models, migration, `FolioService`,
  `api/folios/*` read + charge + payment + void endpoints.
- Money stays integer minor units. Charge/Payment rows are append-only; void only with
  reason + actor + timestamp.
- Providers registered in `bootstrap/providers.php`.
- Feature tests for overlap, pricing, posting idempotency, check-in folio, checkout gate.
- `docs/DECISIONS.md` rows for the open Module B defaults (children board rate, chalet
  modelling, allocation cardinality).

## Why

PDF §5B and Module 2 deep analysis. Module B is the first revenue module and unlocks
Module C (folio), Module D (board inclusion check) and Module G (arrival to pickup).
`work/phases/02-hotel-and-folio.md` payment milestone depends on B + C.

## Acceptance Criteria (complete only when all ticked)

- [x] `Modules/Hotel` + `Modules/Folio` skeleton, providers, tenant migrations
- [x] Room type / room / chalet CRUD with building filter
- [x] Hut dormitory + bed CRUD, bed-level allocation
- [x] Seasonal room price resolve per night (integer cents)
- [x] Four board plans + per person per night price + inclusions payload
- [x] Reservation CRUD with arrival details, availability overlap guard
- [x] Availability + calendar read endpoints (building / room / date)
- [x] Check-in creates Stay + open Folio
- [x] Nightly posting posts room + board charges once per night (idempotent)
- [x] Checkout rejected when folio balance is not zero
- [x] Folio charges / payments / voids append-only with reason + actor
- [x] Feature tests written for overlap, pricing, posting, check-in, checkout gate
- [ ] Tests run + pass (`php artisan test`) — on setup machine
- [x] `work/changes/2026-11-04--72-laravel-hotel-module.md` written
- [x] PLAN.md + SUMMARY.md updated

## Files Likely Affected

- `backend-laravel/Modules/Hotel/**`
- `backend-laravel/Modules/Folio/**`
- `backend-laravel/bootstrap/providers.php`
- `backend-laravel/tests/Support/*`
- `docs/DECISIONS.md`

## Notes / Follow-ups

- Modules are created with `php artisan module:make <Name> --api` only, never by hand.
  `config/modules.php` pins `app_folder` to `App/` and generator paths to `App/...`,
  `Database/migrations/tenant`, `Database/Factories`, `Database/Seeders`, so generated
  modules match `Core` / `Saas` (`Modules\<Name>\App\...`, capital `App`).
- No `tenant_id` columns. Tenancy comes from the active tenant connection.
- Check-out time rules, child board rates, multi-room reservations and cancellation policy
  are open client questions; ship documented defaults, do not block.
- POS board inclusion (Module D) reads `BoardPlan::inclusions()`; no POS code here.

---

## Return to Pending Log (when work stops midway)

### 2026-11-13

- **Returned by**: agent
- **Reason**: User asked for product/API work only (no setup). Item 72 product code already meets ACs; only migrate + `php artisan test` remain (setup). Picked Module 3 Folio (item 76) instead.
- **Next step**: Run migrate + `php artisan test` when user allows setup; then complete 72.

### 2026-11-06

- **Returned by**: agent
- **Reason**: User switched to EQMS menu + Core staff CRUD + Hotel schema focus (item 74); Hotel Laravel code already largely written, tests still pending on setup machine
- **Next step**: Resume 72 for php artisan test / migrate verify when setup allowed


### 2026-11-04

- **Returned by**: agent
- **Reason**: scope split
- **Next step**: Module 3 extends the minimal folio slice (invoice payload, outlet reports, UI)

---

## Completion Log (when work is complete)

- **Completed by**:
- **Date**:
- **What was done**:
- **Files changed**:
- **Verification**:
- **Change report**:
- **PLAN / SUMMARY**:
