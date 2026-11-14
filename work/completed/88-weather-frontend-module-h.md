---
title: Weather frontend — points, limits, warnings, cancel
id: 88
priority: high
status: completed
milestone: 4
module: H-weather
area: frontend
effort: M
dependencies: 83
bug: none
created: 2026-11-14
updated: 2026-11-14
---

# Weather frontend — Module H

## What to do (What)

Wire Nuxt staff UI for Laravel Weather APIs (item 83): weather points, fetch/evaluate,
limits, active warnings, cancel-for-weather, avalanche stub. Menu + nav.

## Current / Before

- Backend `api/weather*` live (item 83).
- Frontend: through Transport. No Weather pages or menu.

## Expected / After

- Pages under `/weather`.
- `useWeatherApiClient`.
- Menu includes Weather for owner/manager and desk roles that see warnings.

## Why

User: next after Weather Laravel module.

## Acceptance Criteria

- [x] Hub + points CRUD + fetch/evaluate
- [x] Limits CRUD
- [x] Warnings list + resolve + cancel-for-weather
- [x] Avalanche stub page
- [x] Menu + nav updated
- [x] Change report + PLAN/SUMMARY
- [x] N/A tests (code-only)

## Files Likely Affected

- `frontend/pages/weather/**`
- `frontend/composables/api-clients/useWeatherApiClient.ts`
- `frontend/utils/app-modules.ts`
- `backend-laravel/Modules/Core/config/menu.php`

---

## Completion Log

- **Completed by**: Agent
- **Date**: 2026-11-14
- **What was done**: Weather Nuxt hub, points/fetch/evaluate, limits, warnings + cancel-for-weather, avalanche stub, menu/nav
- **Files changed**: `frontend/pages/weather/**`, `useWeatherApiClient.ts`, `app-modules.ts`, `Core/config/menu.php`
- **Verification**: code-only; no npm run
- **Change report**: `work/changes/2026-11-14--88-weather-frontend-module-h.md`
- **PLAN / SUMMARY**: synced
