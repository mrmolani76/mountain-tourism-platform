# Change Report: Work 108 — Folio Ledger Integrity

- **Date**: 2026-11-27
- **By**: Copilot
- **Change ID**: 108-folio-ledger-integrity

## 1. Summary
Require active outlet attribution for new charges, map nightly lodging charges, and prevent Folio deletion from cascading through ledger history.

## 2. Before
- `FolioService::postCharge()` accepted missing or inactive outlet IDs.
- Hotel nightly charges omitted `outlet_id`.
- Rental charges had no persisted outlet association.
- Folio, charge, payment, and outlet deletion relationships could remove or detach ledger history.
- Charge forms did not consistently collect an outlet.

## 3. After
- New charge writes require an active outlet on the tenant connection.
- Room nights use the building's Reception outlet, hut beds use Hut Kitchen, and board charges follow the first accommodation allocation by ID.
- Missing nightly outlet configuration is recorded as failed and can be retried. Date lookups use `whereDate()` for consistent retry matching.
- New restrictive foreign keys preserve Folio, charge, payment, and outlet references; legacy null-attributed charges remain unchanged.
- Manual Folio, charge-to-room, rental, and lift-pass forms send outlet IDs; Rental stores its outlet for future return fees.
- Direct room-charge, outlet validation, nightly retry, hut outlet, deletion protection, and weather-void regression coverage was added.

## 4. Reason
The MVP requires each new Charge to identify its Outlet, and append-only money history must not be erased through a parent deletion.

## 5. Files Affected
- `backend-laravel/Modules/Core/Database/Seeders/FoundationSeeder.php`
- `backend-laravel/Modules/Folio/App/Http/Controllers/FolioController.php`
- `backend-laravel/Modules/Folio/App/Http/Requests/ChargeToRoomRequest.php`
- `backend-laravel/Modules/Folio/App/Http/Requests/StoreChargeRequest.php`
- `backend-laravel/Modules/Folio/App/Services/FolioService.php`
- `backend-laravel/Modules/Folio/Database/migrations/tenant/2026_11_27_000001_protect_folio_ledger_foreign_keys.php`
- `backend-laravel/Modules/Hotel/App/Services/NightlyPostingService.php`
- `backend-laravel/Modules/Rental/App/Http/Controllers/RentalContractController.php`
- `backend-laravel/Modules/Rental/App/Http/Requests/RentOutRequest.php`
- `backend-laravel/Modules/Rental/App/Http/Requests/ReturnItemRequest.php`
- `backend-laravel/Modules/Rental/App/Models/Rental.php`
- `backend-laravel/Modules/Rental/App/Services/RentalService.php`
- `backend-laravel/Modules/Rental/Database/migrations/tenant/2026_11_27_000002_add_outlet_id_to_rental_rental.php`
- `backend-laravel/Modules/Services/App/Http/Controllers/BookingController.php`
- `backend-laravel/Modules/Transport/App/Http/Controllers/LiftPassController.php`
- `backend-laravel/Modules/Transport/App/Http/Requests/SellLiftPassRequest.php`
- `backend-laravel/Modules/Transport/App/Services/LiftPassService.php`
- `backend-laravel/tests/Feature/Modules/Hotel/HotelModuleTest.php`
- `frontend/composables/api-clients/useFolioApiClient.ts`
- `frontend/composables/api-clients/useRentalApiClient.ts`
- `frontend/pages/folios/charge-to-room.vue`
- `frontend/pages/folios/view/[id].vue`
- `frontend/pages/rental/contracts/view/[id].vue`
- `frontend/pages/rental/out/index.vue`
- `frontend/pages/rental/rent-out.vue`
- `frontend/pages/transport/passes/index.vue`
- `docs/DECISIONS.md`
- `docs/MVP_SCOPE_AUDIT.md`
- `work/completed/108-protect-folio-ledger-integrity.md`
- `work/PLAN.md`
- `work/SUMMARY.md`
- `work/MEMORY.md`

## 6. Verification / Status
- [x] Focused JUnit report: 6 tests, 29 assertions, 0 failures, 0 errors, 0 skipped.
- [x] PHP syntax checks passed for all modified PHP files.
- [x] `git diff --check` passed.
- [x] Focused HTTP flows: charge-to-room selected-Outlet posting, explicit weather void, and weather cancellation linked-charge void passed.
- [ ] Full `HotelModuleTest.php`: 17 passed, 2 back-to-back reservation checks returned 409 under the SQLite test harness; this is separate from the ledger assertions.
- [ ] PHPUnit process exit remains 1 despite clean focused JUnit reports; this discrepancy was also recorded for item 106.
- [ ] Browser UI check could not run: `http://lab.local.mtmtp.local:3000/` refused the connection. UI acceptance remains in TODO 103 / 121.
- [x] Kanban consistency check passed after running the script with line endings normalized in memory.
- Item 108 completed on 2026-11-27 after TODO 120 removed the endpoint validation blocker.
