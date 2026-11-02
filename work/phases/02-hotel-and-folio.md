# Phase 2 — Hotel and folio

- **PDF**: Milestone 2 (§8)
- **Weeks**: 3–5
- **Payment**: 25%
- **Modules in this phase**: **B + C** (complete descriptions below)
- **Depends on**: Phase 1 (Module A) accepted
- **Focus**: backend / API only. Calendar/front-desk UI deferred.
- **Deep docs**: [B](../../docs/modules/B-hotel-chalets-huts.md) · [C](../../docs/modules/C-guest-folio-and-checkout.md)

---

## Phase goal

Hotel inventory + reservations + stays + nightly posting, and the guest **folio** money core (charges, payments, voids, checkout). All amounts = integer minor units; money append-only.

**Payment unlocks when** Modules B and C “done when” pass on VPS.

---

## Modules in this phase

### Module B — Hotel, chalets and huts (COMPLETE)

**PDF**: §5B, §4 Stay / Board plan

#### Purpose
Sell rooms, chalets, and hut **beds**; take reservations with arrival details; check in to open a Stay + Folio; auto-post room nights and board each night in tenant local time.

#### Actors
| Role | Jobs |
|------|------|
| Front desk | Reservations, calendar data, check-in/out |
| Owner | Room types, prices, board plans |

#### Entities
| Entity | Notes |
|--------|-------|
| RoomType | Per tenant; capacity rules |
| Room | In a building; chalets like rooms |
| HutBed / Bed inventory | Hut dorms sell **single beds**, not whole rooms |
| RoomSeasonPrice | Price per night per season (integer cents) |
| BoardPlan | room_only, B&B, half_board, full_board; price per person per night |
| Reservation | dates, adults, children, room or bed, board plan, deposit, arrival: car/train/plane/bus + time + station |
| Stay | Opened at check-in; links reservation → folio |
| NightlyPostingJob | Posts room night + board charges |

#### Business rules
1. Hut inventory is bed-level, not room-level.
2. Prices and board amounts are integers (minor units).
3. Check-in creates Stay + Folio (Module C).
4. Every night: post room night charge(s) + board charge(s) for the stay; source = room night / board.
5. Meals **included** in board must not be sold again as payable dinner in POS (Module D reads board plan) — B must expose “what’s included” clearly on Stay/BoardPlan.
6. Checkout is Module C’s gate; B ends stay only when folio allows.
7. Arrival mode/time/station stored for Module G pickup creation later.
8. Calendar = query API by building/room/date (UI deferred).

#### API surface (sketch)
- CRUD room types, rooms, beds, season prices, board plans
- Reservations CRUD + availability queries
- POST check-in → Stay + Folio
- POST check-out (delegates folio zero-balance check)
- Nightly posting management command / scheduled job
- Calendar/availability read APIs

#### Acceptance — Module B done when (PDF)
> A 3-night half-board stay posts **3 room nights and 3 board charges**, the included dinners are not charged again, and checkout ends at a **zero balance**.

(Included-dinner part fully proven with Module D; B must post board correctly and expose inclusion.)

#### Package
`apps.hotel`

---

### Module C — Guest folio and checkout (COMPLETE)

**PDF**: §5C, §4 Folio / Charge / Payment; money hard rule

#### Purpose
One bill per stay. Every outlet posts **charges** onto the folio. Staff record **payments**. Never edit/delete money rows — **void** only. Checkout blocked until balance is zero. Invoice payload for print/PDF.

#### Actors
| Role | Jobs |
|------|------|
| Front desk | Folio view, payments, checkout |
| Cashier / others | Charge to room (later modules) |
| Manager | Voids with reason |

#### Entities
| Entity | Notes |
|--------|-------|
| Folio | One per stay; open/closed |
| Charge | Line: outlet, date, amount_cents, tax, source (order, booking, rental, room_night, board, …); immutable |
| Payment | cash or card; amount_cents; immutable; several per folio |
| Void | Links to charge or payment; reason, user_id, timestamp; weather voids use reason `weather` |
| Invoice | Snapshot/payload for print (browser/PDF later) |

#### Business rules (critical)
1. **Append-only**: no UPDATE of amount/outlet on Charge/Payment; no DELETE.
2. Void creates a compensating record / void flag with reason + user + time — never silent overwrite.
3. Balance = sum(active charges) − sum(active payments) (define void math precisely in service).
4. Charge always stores **outlet_id**, tax, source document id.
5. Charge to room: lookup by room number or guest name → open folio.
6. Split tender = multiple Payment rows (half card + half cash = 2 records).
7. Checkout **rejected** while balance ≠ 0.
8. Outlet revenue reports (Module I) read from charge lines; cash reports from payments.
9. Audit log every charge, payment, void.

#### API surface (sketch)
- GET folio by stay/id; list charges/payments
- POST charge (internal service used by B/D/E/F/G)
- POST payment
- POST void (manager)
- POST checkout
- GET invoice payload

#### Acceptance — Module C done when (PDF)
> Summit lunch and ski lesson charged to room; lesson cancelled for wind (void weather); guest pays rest half card half cash; folio closes at zero; each outlet’s report shows only its own sales.

(Ski lesson may need Module F; prove with available sources + void/payment/split first.)

#### Package
`apps.folio`

---

## Phase work packages (seed when Phase 2 starts)

- Hotel rooms/beds/prices/board/reservations APIs
- Check-in → Stay + Folio
- Nightly posting job + B done-when tests
- Charge/Payment/Void services + manager authz
- Checkout gate + invoice payload
- C done-when tests (as far as charge sources allow)

## Phase out of scope

POS UI/tablet, rental, services engine, transport, weather, dashboard UI, §9.
