# Phase 5 — Dashboard and handover (backend focus first)

- **PDF**: Milestone 5 (§8) + handover (§11)
- **Weeks**: 11–12
- **Payment**: 15%
- **Modules**: I (Owner dashboard and reports) + lobby/avalanche from H + §11
- **Depends on**: Phases 1–4 domain data
- **Kanban IDs**: seed when Phase 5 starts (suggested 56–70)
- **Focus**: report/dashboard **APIs**, backups, seed, docs; UI screens deferred until frontend focus returns

## Goal

Owner “today” and date-range report APIs (totals to the cent), lobby read-only data + private link token, avalanche level API, backups/restore proof, full §2 seed, handover artifacts.

## In scope — backend now

- [ ] Today aggregates API: occupancy by building, arrivals/departures + transport, revenue by outlet, rentals out, open folios, lift status, weather warnings
- [ ] Date-range reports: revenue by outlet/service/payment method, weather cancellations; CSV export endpoints
- [ ] Report totals match folio charges + direct sales to the cent (tests)
- [ ] Avalanche danger level 1–5 storage + API (staff morning entry)
- [ ] Lobby: private link token, no-login read API (valley/summit weather, lift status, avalanche, today’s activities)
- [ ] Nightly `pg_dump` backup script; keep last 7; restore tested once
- [ ] Full seed script for §2 demo complex
- [ ] `.env.example` complete; README local + deploy; OpenAPI complete for shipped API
- [ ] Release rollback already from Phase 1 CI — re-verify

## Deferred (frontend)

- [ ] Owner dashboard UI, lobby TV page chrome, report download UX, recorded walkthrough is human process

## Out of scope

- §9 after-MVP product features

## Acceptance (done when)

- Report totals match folio charges + direct sales for same dates, to the cent (§5I)
- Handover checklist (§11) for **backend**: code in GitHub, running on VPS via CI/CD, setup script/guide, README, `.env.example`, Swagger, DB restore + rollback tested, seed script, walkthrough scheduled with client

## Hard constraints

- Totals from charge/payment lines only (no float drift)
- Lobby link must not expose other tenants
- No secrets in repo

## Work packages (backend — seed later)

- Today + report query services + CSV + cent-accurate tests
- Avalanche + lobby token APIs
- Backup/restore scripts + doc proof
- Full §2 seed
- Handover doc pass

## Status

- Not started; wait for Phase 4
- Frontend deferred under current focus
