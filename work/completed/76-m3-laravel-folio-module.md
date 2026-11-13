---
title: Module 3 — Guest folio and checkout (Laravel) full contract
id: 76
priority: high
status: completed
milestone: 2
module: C-folio
area: backend
effort: L
dependencies: 72
bug: none
created: 2026-11-13
updated: 2026-11-13
---

# Module 3 — Guest folio and checkout (Laravel)

## What to do (What)

Extend the Module B minimal folio slice in `backend-laravel/Modules/Folio` to the full
Module C product API from `work/phases/02-hotel-and-folio.md` and
`work/phases/Module_3_Guest_Folio_Checkout_Deep_Analysis.md`:

- Charge-to-room lookup by room number or guest name (in-house stays only).
- Charge-to-room post onto the selected stay folio (outlet + source retained).
- Open folio list / read with balance.
- Invoice payload for browser print (JSON; PDF UI deferred).
- Manager-only voids (already started); keep append-only money rules.

## Current / Before

- Folio has `show` + `storeCharge` + `storePayment` + `storeVoid` only.
- Checkout gate lives on Hotel Stay (`CheckoutService`), not a Folio invoice surface.
- No charge-to-room search or post endpoint.
- No invoice payload endpoint.

## Expected / After

- `GET api/folios` list (status filter, balance).
- `GET api/folios/charge-to-room` search by `room_number` and/or `guest`.
- `POST api/folios/charge-to-room` posts a charge to a selected in-house stay folio.
- `GET api/folios/{folio}/invoice` returns printable invoice JSON payload.
- Folio authz lives in Folio module (`FolioAuthz`); cashier included for charge-to-room.
- Money remains integer minor units, append-only, void with reason + actor + timestamp.

## Why

Phase 2 payment unlock needs Modules B + C. Item 72 shipped the minimal slice; Module 3
is the next product/API work (user: no setup).

## Acceptance Criteria (complete only when all ticked)

- [x] Folio list endpoint with status filter + balance_cents
- [x] Charge-to-room search by room_number and/or guest name (in-house only)
- [x] Charge-to-room post requires explicit stay_id when multiple matches
- [x] Invoice JSON payload (charges, payments, voids, balance, guest/stay refs)
- [x] Manager+ only for voids; folio clerks for charge/payment/search
- [x] No migrate / Docker / test-run in this item (code-only per user)
- [x] `work/changes/2026-11-13--76-laravel-folio-module-c.md` written
- [x] PLAN.md + SUMMARY.md updated

## Files Likely Affected

- `backend-laravel/Modules/Folio/**`
- `docs/DECISIONS.md` (if new Module C defaults)

## Notes / Follow-ups

- Outlet revenue reports stay Module I.
- PDF generation / browser print UI deferred (payload only).
- Item 72 remains in todo for migrate + test when setup allowed.

---

## Return to Pending Log (when work stops midway)

---

## Completion Log (when work is complete)

- **Completed by**: Agent
- **Date**: 2026-11-13
- **What was done**: Module C Folio APIs — list, charge-to-room search/post, invoice JSON, FolioAuthz
- **Files changed**: Folio module services/controller/routes/requests + DECISIONS + kanban
- **Verification**: N/A code-only (user: no setup / no test run)
- **Change report**: `work/changes/2026-11-13--76-laravel-folio-module-c.md`
- **PLAN / SUMMARY**: yes
