# Phase 3 — POS, rental, services (backend focus)

- **PDF**: Milestone 3 (§8)
- **Weeks**: 6–8
- **Payment**: 25%
- **Modules**: D (Restaurants/huts/shop) + E (Rental) + F (Services/activities)
- **Depends on**: Phase 2 (folio/charge-to-room)
- **Kanban IDs**: seed when Phase 3 starts (suggested 26–40)
- **Focus**: backend APIs and domain rules; tablet UI deferred

## Goal

Outlet products/orders, rental lifecycle, and one generic service booking engine — all server-side with tests against PDF done-when.

## In scope — backend now

### D — Restaurants, huts, shop
- [ ] Products with categories, price, tax; menus by season
- [ ] Orders with item notes, table number; board-check against guest board plan
- [ ] Packed lunches (order evening before; kitchen list payload)
- [ ] Pay now or charge to room; cashier shift open/close with cash count
- [ ] Kitchen ticket / receipt payloads (print later on frontend)

### E — Equipment rental
- [ ] Item types; items with size + code; day price by season; deposit
- [ ] Rent out (hotel guest or walk-in), return, damage fee
- [ ] Currently out list; overdue detection
- [ ] Prevent double rent of same item

### F — Services and activities
- [ ] One booking engine for all service types (no per-service modules)
- [ ] Service: duration, capacity, price or included, min age, staff role needed, indoor/outdoor, building
- [ ] Slots by weekday/season; staff assigned; conflict rules
- [ ] Bookings; pay now or charge to room; cancel removes charge
- [ ] Guided outing headcount: open until all marked back

## Deferred (frontend)

- [ ] Tablet POS screens, 80 mm print UI, rental desk UI, service desk UI

## Out of scope this phase

- Transport, lifts, weather, dashboard (Phases 4–5)
- §9 after-MVP

## Acceptance (done when)

- Half-board dinner recorded as included; wine at same table charged to room (§5D)
- One item cannot be rented twice; late return adds extra day (§5E)
- Sauna cannot over capacity; instructor not double-booked; cancelled booking removes charge; outing with missing participant cannot close (§5F)

## Hard constraints

- All charges append-only via folio services from Phase 2
- Tenant isolation + API permissions
- Integer money

## Work packages (backend — seed later)

- Product/menu/order/shift APIs + board-check service
- Packed lunch APIs + kitchen list endpoint
- Rental inventory + rent/return/damage + tests
- Service/Slot/Booking engine + guided outing state machine + tests

## Status

- Not started; wait for Phase 2
- Frontend deferred
