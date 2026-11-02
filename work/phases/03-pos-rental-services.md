# Phase 3 — POS, rental, services

- **PDF**: Milestone 3 (§8)
- **Weeks**: 6–8
- **Payment**: 25%
- **Modules in this phase**: **D + E + F** (complete descriptions below)
- **Depends on**: Phase 2 (folio charge-to-room, board plan on stay)
- **Focus**: backend / API only. Tablet POS UI deferred.
- **Deep docs**: [D](../../docs/modules/D-restaurants-huts-shop.md) · [E](../../docs/modules/E-equipment-rental.md) · [F](../../docs/modules/F-hotel-services-and-activities.md)

---

## Phase goal

Outlet commerce (F&B/shop), equipment rental lifecycle, and one generic bookable **Service/Slot** engine including guided outing headcount — all posting money through Module C.

---

## Modules in this phase

### Module D — Restaurants, huts and shop (COMPLETE)

**PDF**: §5D

#### Purpose
Products and orders at any altitude outlet (valley restaurant, mid self-service, summit, hut kitchen, bar, gear shop). Board-check against hotel guest. Packed lunches. Pay now or charge to room. Cashier shifts.

#### Actors
| Role | Jobs |
|------|------|
| Cashier | Orders, pay now, charge to room, shifts |
| Manager | Products, prices, voids (via C) |

#### Entities
| Entity | Notes |
|--------|-------|
| ProductCategory / Product | price_cents, tax; visible per season |
| Order / OrderLine | table number, note per item, outlet |
| BoardCheck | given stay/guest → meal included or not |
| PackedLunchOrder | ordered evening before; morning kitchen list |
| CashierShift | open/close + cash count |
| Print payloads | kitchen ticket, 80mm receipt (JSON/HTML for later UI) |

#### Business rules
1. Menu visibility by season.
2. When cashier selects hotel guest: show whether **this meal** is included in board plan; included → record as included (no guest charge or zero payable charge per product rules); extras (wine) → Charge on folio.
3. Pay now → Payment (and/or direct sale record consistent with reports); charge to room → Charge on folio with outlet.
4. Packed lunches: deadline evening before; kitchen list endpoint for morning.
5. Shift open required before POS sales (recommended rule — document if enforced).

#### Acceptance — Module D done when (PDF)
> Half-board guest’s dinner recorded as **included**; bottle of wine at same table **charged to the room**.

#### Package
`apps.pos`

---

### Module E — Equipment rental (COMPLETE)

**PDF**: §5E, §4 Rental

#### Purpose
Rent skis/bikes/etc. to hotel guest or walk-in; return; damage fee; prevent double-rent; overdue extra day on bill.

#### Actors
| Role | Jobs |
|------|------|
| Rental desk | Rent out, return, damage |

#### Entities
| Entity | Notes |
|--------|-------|
| RentalItemType | skis, boots, bike, … (complex-defined) |
| RentalItem | size, item code, status in/out |
| RentalRate | price per day per season; deposit_cents |
| Rental | out→return; guest or walk-in; folio link optional |
| DamageFee | charge on return |

#### Business rules
1. One physical item cannot be OUT twice overlapping.
2. Day pricing by season; late return adds extra day charge.
3. Deposit handling documented (hold vs charge).
4. Hotel guest → charges on folio; walk-in → pay now path.
5. List currently out; overdue highlighted (query flag).

#### Acceptance — Module E done when (PDF)
> One item cannot be rented twice at the same time; late return adds the extra day to the bill.

#### Package
`apps.rental`

---

### Module F — Hotel services and activities (COMPLETE)

**PDF**: §5F, §4 Service / Slot

#### Purpose
**One** booking engine for all services (sauna, spa, ski school, guided hikes, …). Complex configures services — no separate codebase per service type. Slots, staff, capacity, guided outing headcount.

#### Actors
| Role | Jobs |
|------|------|
| Service staff | Slots, bookings, outing headcount |
| Front desk | Book on behalf of guest |

#### Entities
| Entity | Notes |
|--------|-------|
| Service | duration, capacity, price or included_in_stay, min_age, required staff role, indoor/outdoor, building |
| Slot | weekday/season schedule instance; capacity; assigned staff |
| Booking | guest/stay; pay now or charge to room |
| GuidedOuting | participants checked out at start, checked in at end; stays open until all back |

#### Business rules
1. Do not build per-service modules — configuration only.
2. Slot capacity cannot be exceeded.
3. One staff member cannot be on two overlapping slots.
4. Cancel booking → remove/void related charge (Module C).
5. Outdoor slots participate in weather warnings (Module H).
6. Outing cannot close while any participant still out.

#### Acceptance — Module F done when (PDF)
> Sauna slot cannot go over capacity; one instructor cannot be in two lessons at once; cancelled booking removes its charge; outing with missing participant cannot be closed.

#### Package
`apps.services`

---

## Phase work packages (seed later)

- POS products/orders/board-check/shifts/packed lunch APIs + D tests
- Rental inventory + rent/return/damage + E tests
- Service/Slot/Booking/Outing engine + F tests

## Phase out of scope

Transport, lifts, weather engine, owner dashboard, frontend tablets, §9.
