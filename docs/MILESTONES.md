# Milestones and payments (MVP §8)

Each payment releases when the milestone runs on the client VPS and passes module “done when” checks.

| # | Milestone | Delivers | Weeks | Payment |
|---|-----------|----------|-------|---------|
| 1 | Foundation | Data model + architecture note, VPS setup, CI/CD auto-deploy, login, tenants, buildings+altitude, outlets, staff/roles, seasons, super admin | 1–2 | 15% |
| 2 | Hotel and folio | Rooms/chalets/hut beds, season prices, board plans, reservations, calendar, check-in/out, nightly posting, folio, payments, voids, invoice | 3–5 | 25% |
| 3 | POS, rental, services | Restaurant/hut/shop POS + board check, packed lunches, rental, service booking engine, guided outing headcount | 6–8 | 25% |
| 4 | Transport, lifts, weather | Transport types, vehicles/drivers/routes, shuttles/taxi/pickups, lifts+status+passes, weather points/forecasts/limits/warnings/cancel | 9–10 | 20% |
| 5 | Dashboard and handover | Owner dashboard/reports, lobby screen, avalanche, backups, bugfix, handover (§11) | 11–12 | 15% |

After final handover: bugs in scope fixed free for 30 days.

## Phase plans
Executable phase breakdown: `work/phases/` (README + `01`…`05`).  
Kanban items: `work/todo/` with `milestone: N` (Phase 1 backend packages = IDs 02–10).

## Current delivery focus
**Backend / API only** (Django + DRF). Frontend (Nuxt/Vuetify) deferred until explicitly opened. Phase “done when” UI checks wait for a later frontend pass; automated API/tests still prove Milestone 1 isolation and later money rules.

## Work tracking tip
Prefix work items with milestone: `02-m1-architecture-data-model.md`, `12-m2-folio-void.md`, etc.
