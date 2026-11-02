# Phase 2 — Hotel and folio (backend focus)

- **PDF**: Milestone 2 (§8)
- **Weeks**: 3–5
- **Payment**: 25%
- **Modules**: B (Hotel, chalets, huts) + C (Guest folio and checkout)
- **Depends on**: Phase 1 acceptance
- **Kanban IDs**: seed when Phase 2 starts (suggested 11–25)
- **Focus**: backend APIs, posting jobs, money rules; UI deferred

## Goal

Hotel inventory, reservations, stays, nightly posting, folio/charges/payments/voids, checkout gate — all as **API + services + tests**. Money = integer minor units, append-only.

## In scope — backend now

- [ ] Room types and rooms in any building; hut dorms sell beds
- [ ] Price per night per season (integers)
- [ ] Board plans: room only, B&B, half board, full board (price per person per night)
- [ ] Reservations: dates, adults/children, room or bed, board plan, deposit, arrival mode/time/station
- [ ] Check-in opens Stay + Folio; nightly job posts room nights + board
- [ ] Folio lists charges (outlet, date, amount, tax, source)
- [ ] Charge to room by room number or guest name (API)
- [ ] Payments cash/card recorded (no gateway); multiple per folio
- [ ] Void charge with reason + user + timestamp (manager permission); never edit/delete money rows
- [ ] Checkout blocked until balance zero; invoice data for print/PDF
- [ ] Audit every charge, payment, void

## Deferred (frontend)

- [ ] Calendar grid UI; front-desk screens; browser invoice print chrome

## Out of scope this phase

- POS, rental, services, transport, lifts, weather, owner dashboard (Phases 3–5)
- §9 after-MVP

## Acceptance (done when)

- 3-night half-board stay posts 3 room nights + 3 board charges; included dinners not charged again; checkout ends at zero balance (§5B).
- Summit lunch + ski lesson charged to room; lesson voided for weather; half card + half cash; folio closes at zero; outlet reports from charges only (§5C) — ski lesson charge source may stub until Phase 3 services exist; prefer folio/void/payment proof with available charge sources.

## Hard constraints

- Integer minor units; append-only money; void only
- Tenant-scoped queries via single isolation layer
- Nightly posting uses tenant local timezone

## Work packages (backend — seed later)

- Room/bed models + season prices APIs
- Board plans + reservation APIs
- Check-in / Stay / Folio create
- Nightly posting command/job + tests
- Charge/Payment/Void services + manager authz
- Checkout zero-balance + invoice payload
- Automated tests for §5B (and §5C as far as charge sources allow)

## Status

- Not started; wait for Phase 1
- Frontend deferred
