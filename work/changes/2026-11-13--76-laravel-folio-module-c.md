# Change Report: Module 3 Folio charge-to-room + invoice payload

- **Date**: 2026-11-13
- **By**: Agent
- **Change ID**: 76

## 1. Summary

Extended `Modules/Folio` from the Module B minimal slice to Module C product APIs:
folio list, charge-to-room search/post, and printable invoice JSON.

## 2. Before

- Folio routes: `GET folios/{id}`, `POST charges|payments|voids` only.
- Authz imported from `HotelAuthz`.
- No charge-to-room lookup; no invoice payload; no folio list.

## 3. After

- `GET api/folios` (status/reference filter + `balance_cents` + `stay_id`)
- `GET api/folios/charge-to-room?room_number=&guest=`
- `POST api/folios/charge-to-room` (required `stay_id` + charge fields)
- `GET api/folios/{folio}/invoice` JSON payload (charges, payments, voids, totals, guest/room)
- `FolioAuthz` for clerk / charge-to-room / manager+ void gates
- `ChargeToRoomService` + `InvoiceService`
- Decision row: always require `stay_id` on charge-to-room POST

## 4. Reason

User asked for next module product/API work only (no setup). Item 72 product code was
already complete; Module C is the Phase 2 next revenue surface.

## 5. Files Affected

- `backend-laravel/Modules/Folio/App/Support/FolioAuthz.php` (new)
- `backend-laravel/Modules/Folio/App/Services/ChargeToRoomService.php` (new)
- `backend-laravel/Modules/Folio/App/Services/InvoiceService.php` (new)
- `backend-laravel/Modules/Folio/App/Http/Requests/ChargeToRoomRequest.php` (new)
- `backend-laravel/Modules/Folio/App/Http/Controllers/FolioController.php`
- `backend-laravel/Modules/Folio/App/Services/FolioService.php` (docblock)
- `backend-laravel/Modules/Folio/routes/api.php`
- `docs/DECISIONS.md`
- `work/todo/72-*.md` (returned; setup deferred)
- `work/inprogress/76-*.md` → completed
- `work/PLAN.md`, `work/SUMMARY.md`, `work/MEMORY.md`

## 6. Verification / Status

- [ ] Tests not run (user: no setup / code-only)
- [ ] Migrations: none new (reuse existing folio tables)
- [x] Money append-only unchanged; void still Manager+
- [x] Charge-to-room only for `in_house` stays with open folio
- [x] Tenant isolation via active tenant connection (no cross-tenant queries)
