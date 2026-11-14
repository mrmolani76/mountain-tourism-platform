# Change Report: Transport frontend Module G

- **Date**: 2026-11-14
- **By**: Agent
- **Change ID**: 87

## 1. Summary

Shipped Nuxt staff UI for Laravel Transport: fleet, trips, reservation pickup,
passenger list, lifts status, lobby board, lift pass sell, and menu.

## 2. Before

- Backend `api/transport*` existed (item 81).
- Frontend had Core + Hotel + Folio + POS + Rental + Services only.

## 3. After

- `useTransportApiClient` for fleet/trips/lifts/passes.
- Pages under `/transport`.
- Menu + static nav include Transport for desk / lift roles.

## 4. Reason

User: next start after Services frontend.

## 5. Files Affected

- `frontend/composables/api-clients/useTransportApiClient.ts`
- `frontend/pages/transport/**`
- `frontend/utils/app-modules.ts`
- `backend-laravel/Modules/Core/config/menu.php`
- work kanban 87

## 6. Verification / Status

- [x] Code-only (no npm run)
- [ ] Browser E2E deferred
