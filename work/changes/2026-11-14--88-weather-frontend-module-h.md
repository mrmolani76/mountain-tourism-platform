# Change Report: Weather frontend Module H

- **Date**: 2026-11-14
- **By**: Agent
- **Change ID**: 88

## 1. Summary

Shipped Nuxt staff UI for Laravel Weather: points, limits, warnings,
cancel-for-weather, avalanche stub, and menu.

## 2. Before

- Backend `api/weather*` existed (item 83).
- Frontend had Transport but no Weather pages or menu entries.

## 3. After

- `useWeatherApiClient` for points/fetch/evaluate/limits/warnings/cancel/avalanche.
- Pages under `/weather` (hub, points, limits, warnings, avalanche).
- Core menu + static `weatherNavigation` for owner/manager/desk roles.

## 4. Reason

User: next after Weather Laravel module. Hotel 72 still blocked on setup tests.

## 5. Files Affected

- `frontend/composables/api-clients/useWeatherApiClient.ts`
- `frontend/pages/weather/**`
- `frontend/utils/app-modules.ts`
- `backend-laravel/Modules/Core/config/menu.php`
- work kanban 88

## 6. Verification / Status

- [x] Code-only (no npm run)
- [ ] Browser E2E deferred
