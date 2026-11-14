---
title: Transport frontend — trips, lifts, pickup, lift passes
id: 87
priority: high
status: completed
milestone: 4
module: G-transport
area: frontend
effort: M
dependencies: 81
bug: none
created: 2026-11-14
updated: 2026-11-14
---

# Transport frontend — Module G

## What to do (What)

Wire Nuxt staff UI for Laravel Transport APIs (item 81): vehicles, drivers,
routes, trips (create/pickup/passengers/passenger-list), lifts + status,
lift pass sell. Menu + nav.

## Current / Before

- Backend `api/transport*` live.
- Frontend: through Services. No Transport pages.

## Expected / After

- Pages under `/transport`.
- `useTransportApiClient`.
- Menu includes Transport for transport desk / lift operator roles.

## Why

User: next after Services frontend.

## Acceptance Criteria
- [ ] Hub + fleet (vehicles/drivers/routes)
- [ ] Trips desk + pickup from reservation + passenger list
- [ ] Lifts status + lift pass sell
- [ ] Menu + nav updated
- [x] Change report + PLAN/SUMMARY
- [ ] N/A tests (code-only)

---

## Completion Log

- **Completed by**: Agent
- **Date**: 2026-11-14
- **What was done**: Transport Nuxt hub, fleet, trips/pickup/passenger list, lifts/lobby/passes, menu
- **Files changed**: `frontend/pages/transport/**`, `useTransportApiClient.ts`, `app-modules.ts`, `Core/config/menu.php`
- **Verification**: code-only; no npm run
- **Change report**: `work/changes/2026-11-14--87-transport-frontend-module-g.md`
- **PLAN / SUMMARY**: synced
