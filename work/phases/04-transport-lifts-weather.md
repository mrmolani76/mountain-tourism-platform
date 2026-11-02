# Phase 4 — Transport, lifts, weather

- **PDF**: Milestone 4 (§8)
- **Weeks**: 9–10
- **Payment**: 20%
- **Modules in this phase**: **G (full) + H (core engine)** — complete descriptions below
- **Depends on**: Phase 2 folio (passes/voids); Phase 3 helpful for outdoor slots
- **Focus**: backend / API + weather job. Map/lift-operator UI deferred.
- **Deep docs**: [G](../../docs/modules/G-transport-and-lifts.md) · [H](../../docs/modules/H-weather.md)
- **Note**: Lobby TV page + avalanche **staff entry** finish in Phase 5 per PDF §8 table, but H describes them; Phase 4 ships APIs/data H needs.

---

## Phase goal

Move people (own transport, lifts, external pickups) and run weather as a **core** server feature: MET cache, limits, warnings, cancel-for-weather voids.

---

## Modules in this phase

### Module G — Transport and lifts (COMPLETE)

**PDF**: §5G, §4 Transport type / Trip / Lift / Lift pass

#### Purpose
Three transport groups with different behavior. Own transport: vehicles, drivers, map-drawn routes, trips, bookings. Lifts: status between buildings + pass products. External: record arrival/departure and create pickup/drop-off trips. No driver login; passenger list print payload. No gate hardware.

#### Transport groups

| Group | Examples | System behavior |
|-------|----------|-----------------|
| Own transport | Shuttle, taxi, private car, bicycle, motorbike | Vehicles, drivers, schedules, trips, bookings. **Bikes/motorbikes go through rental (E)**, not trip fleet |
| Lifts | Cable car, gondola, chairlift, funicular, elevator | Status, hours, lift passes |
| External | Plane, train, metro, intercity bus | Arrival/departure on reservation → pickup/drop-off Trip |

#### Actors
| Role | Jobs |
|------|------|
| Transport desk | Vehicles, drivers, routes, trips, bookings, passenger lists |
| Lift operator | Lift status from phone (API; UI deferred) |
| Front desk | Sell lift passes; reservation arrival feeds pickups |

#### Entities
| Entity | Notes |
|--------|-------|
| TransportType | group = own \| lift \| external |
| Vehicle | seats; own transport |
| Driver | no login; assigned to trips |
| Route | stops + path drawn by staff; **no** road routing service |
| Trip | shuttle / taxi / pickup; vehicle, driver, seats, time |
| TripBooking | cannot overbook seats |
| Lift | between two buildings; type; hours; status open/closed/on_hold |
| LiftStatusChange | time + reason history |
| LiftPassProduct | 1 day, 6 days, pedestrian, adult/child, … |
| LiftPassSale | POS or front desk; charge to room |

#### Business rules
1. Driver cannot be on two overlapping trips; same for vehicle.
2. Trip bookings ≤ seats.
3. Reservation “arrives by train at 14:20 at valley” → create pickup Trip for transport desk.
4. Same for departures → drop-off.
5. Lift status changes always logged with time + reason.
6. Closed lift must be readable by lobby consumers within ~1 minute (status API / cache).
7. Pass sales create Charges (Module C) with lifts outlet.
8. Passenger list = printable payload; drivers never log in.

#### Acceptance — Module G done when (PDF)
> Driver cannot be double-booked; train arrival on a reservation creates the pickup trip; lift set to closed shows on the lobby screen within a minute.

(Lobby **page** may be Phase 5; Phase 4 must expose status suitable for lobby.)

#### Package
`modules.transport`

---

### Module H — Weather (COMPLETE for Phase 4 core; lobby/avalanche UI in Phase 5)

**PDF**: §5H; Milestone 4 delivers weather points/forecasts/limits/warnings/cancel; Milestone 5 lists lobby + avalanche

#### Purpose
Weather is core, not a widget. Each building/lift station is a weather point. Server fetches MET Norway hourly, stores cache, evaluates limits, attaches warnings to lifts/trips/outdoor slots. Staff decide cancellations; system voids charges with reason `weather`.

#### Actors
| Role | Jobs |
|------|------|
| Owner | Set weather limits per point |
| Transport / service / lift | See warnings; cancel for weather |
| Staff (Phase 5) | Enter avalanche 1–5 daily |
| Public lobby (Phase 5) | Read-only TV via private link |

#### Entities
| Entity | Notes |
|--------|-------|
| WeatherPoint | building/lift station; coords; altitude |
| WeatherForecastSnapshot | cached MET data; fetched_at; now / 48h / ~9d fields |
| WeatherLimit | per tenant per point (gusts, precip/day, fog, low temp, …) |
| WeatherWarning | attached to lifts, trips, outdoor slots in time window |
| WeatherCancellation | cancels bookings; voids charges reason `weather`; guest notify list |
| AvalancheLevel | 1–5; morning staff entry (Phase 5 deliverable) |
| LobbyLink | private token, no login (Phase 5 page; API can start here) |

#### MET Norway rules
- Free, no API key; Locationforecast; altitude-aware
- Hourly background job; honor caching + User-Agent
- If API down: keep last data + timestamp on screen
- Credit: “Weather data from MET Norway”
- Maps later: OSM credit

#### Forecast fields (per point)
Temperature, wind, gusts, direction, precip as snow/rain from temp at altitude, cloud/fog, humidity, UV — now, next 48h hourly, ~9 days.

#### Business rules
1. Browsers **never** call MET — server only, shared cache.
2. Crossing a limit → warnings on affected lifts, trips, outdoor service slots.
3. System **never auto-cancels**. “Cancel for weather” is explicit.
4. Cancel → cancel bookings + void charges (`weather`) + guest list payload.
5. People decide; folios stay correct after voids.

#### Acceptance — Module H done when (PDF)
> Lowering a limit makes warnings appear on matching lift, trips and outdoor slots; a weather cancellation leaves every affected folio correct.

#### Package
`modules.weather`

---

## Phase work packages (seed later)

- Transport types/vehicles/drivers/routes/trips + conflict tests
- Reservation arrival → pickup
- Lifts + status history + pass sales
- Weather points + MET job + limits/warnings + cancel-for-weather
- Status API for lobby consumer

## Phase out of scope

Owner dashboard UI, full handover, §9; avalanche/lobby **screens** (Phase 5) though APIs may land early.
