# Phase 5 — Dashboard and handover

- **PDF**: Milestone 5 (§8) + §11 handover
- **Weeks**: 11–12
- **Payment**: 15%
- **Modules in this phase**: **I (full)** + **H leftovers** (lobby screen, avalanche entry) + handover ops
- **Depends on**: Phases 1–4 data (charges, payments, lifts, weather, rentals, …)
- **Focus**: backend report/lobby/avalanche APIs + ops scripts first; dashboard/lobby UI deferred under current focus
- **Deep docs**: [I](../../docs/modules/I-owner-dashboard-and-reports.md) · [H](../../docs/modules/H-weather.md)

---

## Phase goal

Owner operational visibility and money-true reports; lobby TV data; avalanche level; backups; full §2 seed; handover checklist. After this, 30-day free bugfix on in-scope MVP.

---

## Modules in this phase

### Module I — Owner dashboard and reports (COMPLETE)

**PDF**: §5I

#### Purpose
“Today” snapshot for the complex, plus date-range reports and CSV. Totals must match folio charges + direct sales **to the cent** (integer math).

#### Actors
| Role | Jobs |
|------|------|
| Owner | Today dashboard + reports |
| Manager | Outlet reports (subset) |

#### “Today” aggregates (API)
- Occupancy by building
- Arrivals and departures with their transport
- Revenue by outlet
- Rentals currently out
- Open folios
- Lift status
- Weather warnings

#### Date-range reports
- Revenue by outlet
- Revenue by service
- Revenue by payment method
- Weather cancellations
- CSV export endpoints

#### Business rules
1. Outlet sales from **charge** lines (non-voided).
2. Cash/card from **payment** lines.
3. No float drift — integer cents end-to-end.
4. Tenant timezone for “today” and range bounds.
5. Manager sees only assigned outlets if required by role rules.

#### Acceptance — Module I done when (PDF)
> Report totals match the folio charges plus direct sales for the same dates, **to the cent**.

#### Package
`modules.reports`

---

### Module H leftovers — Lobby + avalanche (COMPLETE for Phase 5 delivery)

**PDF**: §5H bullets on avalanche + lobby; §8 Milestone 5 line items

#### Avalanche
- Level 1–5 entered by staff each morning from official regional bulletin
- Stored per tenant per date; shown on lobby + relevant dashboards

#### Lobby screen
- Read-only page for TV in lobby / valley station
- Opened by **private link**, **no login**
- Shows: weather at valley and summit, lift status, avalanche level, today’s activities
- Backend: tokenized public read API; must not leak other tenants
- Frontend TV page deferred under current focus; API required for Milestone 5

#### Acceptance (with H core from Phase 4)
Lobby consumers see lift closed within a minute (G); weather credits on weather payloads.

---

### Handover block (§11) — not a product module, but Phase 5 scope

Complete when:

- [ ] All code in client GitHub
- [ ] Running on VPS, deployed by CI/CD
- [ ] Server setup script/guide for fresh VPS
- [ ] README: local setup + deploy
- [ ] `.env.example` every variable explained
- [ ] API docs (Swagger/OpenAPI)
- [ ] DB restore tested once; release rollback tested once
- [ ] Seed script = full demo complex from PDF §2
- [ ] ~1h recorded walkthrough call

Also: nightly `pg_dump`, keep last 7 backups (PDF §6).

---

## Phase work packages (seed later)

- Today + report query services + CSV + cent-accurate tests (I)
- Avalanche API + lobby token read API (H leftovers)
- Backup/restore scripts + proof notes
- Full §2 seed command
- Handover doc pass (README, env example, OpenAPI completeness)

## Phase out of scope

§9 after-MVP (guest portal, RFID, offline, SMS, paid weather, OTA, SaaS billing, …). Design must not block them.
