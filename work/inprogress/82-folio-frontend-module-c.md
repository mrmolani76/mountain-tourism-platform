---
title: Folio frontend — list, detail, charge-to-room, pay, void
id: 82
priority: high
status: inprogress
milestone: 2
module: C-folio
area: frontend
effort: M
dependencies: 76
bug: none
created: 2026-11-13
updated: 2026-11-13
---

# Folio frontend — Module C

## What to do (What)

Wire Nuxt staff UI for Laravel Folio APIs already shipped in item 76:
list, detail (charges/payments/voids), charge-to-room search + post,
record payment, void with reason, invoice JSON view. Menu + nav.

## Current / Before

- Backend: `api/folios*` live.
- Frontend: stay-embedded read-only folio only (`StayViewFolio`, stays dialog).
- Menu: Core + Hotel only.

## Expected / After

- Pages under `/folios` for list, detail, charge-to-room.
- `useFolioApiClient` for all folio money calls.
- Menu + static nav include Folio for front desk roles.
- Stay folio links to full folio page.

## Why

User: frontend for completed modules, one module complete + commit.
Folio is the money hub after Hotel.

## Acceptance Criteria (complete only when all ticked)
- [ ] Folio list + detail pages
- [ ] Charge-to-room search + post
- [ ] Payment + void (reason) from detail
- [ ] Invoice JSON view / printable section
- [ ] Menu + app navigation updated
- [ ] Change report + PLAN/SUMMARY
- [ ] N/A tests (code-only frontend)

## Files Likely Affected
- `frontend/pages/folios/**`
- `frontend/composables/api-clients/useFolioApiClient.ts`
- `frontend/utils/app-modules.ts`
- `backend-laravel/Modules/Core/config/menu.php`

## Notes / Follow-ups
- Money stays append-only; UI never edits charges/payments.
- POS/Rental/Services frontend deferred to later items.

---

## Completion Log

- **Completed by**:
- **Date**:
- **What was done**:
- **Files changed**:
- **Verification**:
- **Change report**:
- **PLAN / SUMMARY**:
