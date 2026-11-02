# Phase 4 — Transport, lifts, weather (backend focus)

- **PDF**: Milestone 4 (§8)
- **Weeks**: 9–10
- **Payment**: 20%
- **Modules**: G (Transport and lifts) + H core (Weather engine)
- **Depends on**: Phase 3 (optional charge sources); Phase 2 folio required for voids/passes
- **Kanban IDs**: seed when Phase 4 starts (suggested 41–55)
- **Focus**: backend APIs, jobs, map data storage; lobby UI + avalanche entry in Phase 5

## Goal

Transport types/trips/pickups, lifts + status history + passes, MET Norway cached forecasts, limits, warnings, cancel-for-weather voids — server-side.

## In scope — backend now

### G — Transport and lifts
- [ ] Transport types in three groups: own, lifts, external
- [ ] Own: vehicles, drivers, routes (stops + path drawn by staff; no routing service), trips, bookings, seat limits
- [ ] No driver/vehicle double-book; printable passenger list payload
- [ ] External arrival/departure on reservation creates pickup/drop-off trip
- [ ] Lifts between buildings: type, hours, status (open/closed/on hold) with history (time + reason)
- [ ] Lift passes as products; sell via API; charge to room
- [ ] Bicycles/motorbikes via rental (Phase 3), not own-transport trips

### H core — Weather
- [ ] Every building/lift station is a weather point (coords + altitude)
- [ ] MET Norway Locationforecast hourly job; cache; User-Agent + caching rules; stale-on-failure
- [ ] Now / 48h hourly / ~9 day fields as in PDF
- [ ] Limits per complex per point; warnings on lifts, trips, outdoor slots in window
- [ ] Never auto-cancel; “cancel for weather” cancels bookings, voids charges reason `weather`, guest notify list payload
- [ ] Credits strings for MET Norway (and OSM when maps appear)

## Deferred (frontend)

- [ ] Map drawing UI, lift operator phone UI, weather screens, lobby TV (lobby = Phase 5)
- [ ] Avalanche level staff entry UI (Phase 5; API may stub early if needed)

## Out of scope this phase

- Owner dashboard/reports polish, handover (§ Phase 5)
- Gate hardware, paid weather, §9 items

## Acceptance (done when)

- Driver cannot be double-booked; train arrival on reservation creates pickup; lift set to closed is visible to lobby consumers within a minute (§5G) — lobby page itself may wait until Phase 5; expose status API suitable for lobby.
- Lowering a limit makes warnings on matching lift/trips/outdoor slots; weather cancellation leaves folios correct (§5H)

## Hard constraints

- Weather fetched **only on server**, shared cache
- Free MET Norway only; no API keys
- Money voids append-only with reason `weather`

## Work packages (backend — seed later)

- Transport type/vehicle/driver/route/trip APIs + conflict tests
- Reservation arrival → pickup trip
- Lift + status history + pass products
- Weather point + MET fetch job + store
- Limits/warnings engine + cancel-for-weather service + tests

## Status

- Not started; wait for Phase 3
- Frontend deferred; lobby/avalanche finish in Phase 5
