# Module B — Hotel, chalets and huts

- **Phase**: 2 — see full embed in [`work/phases/02-hotel-and-folio.md`](../../work/phases/02-hotel-and-folio.md)
- **PDF**: §5B
- **Depends on**: A
- **Unlocks**: C (folio at check-in), D (board check), G (arrival → pickup)

## Purpose
Rooms, chalets, hut beds, season prices, board plans, reservations, check-in → Stay, nightly room/board posting.

## Entities
RoomType, Room, HutBed, RoomSeasonPrice, BoardPlan, Reservation (arrival mode/time/station), Stay, NightlyPostingJob.

## Rules (summary)
- Huts sell beds, not whole rooms
- Integer night/board prices
- Check-in opens Stay + Folio (C)
- Nightly job posts room nights + board in tenant TZ
- Board inclusion must be queryable for POS (D)

## APIs
CRUD inventory/prices/board/reservations; availability/calendar reads; check-in/out; nightly command.

## Done when
3-night half-board → 3 room + 3 board charges; included dinners not charged again; checkout at zero balance.

## Tests
Posting correctness; bed vs room inventory; timezone boundary around midnight; checkout blocked if folio non-zero (with C).

## Package
`modules.hotel`
