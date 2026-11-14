# Change Report: Folio frontend Module C

- **Date**: 2026-11-13
- **By**: Agent
- **Change ID**: 82

## 1. Summary

Shipped Nuxt staff UI for Laravel Folio APIs: list, detail (pay/charge/void),
charge-to-room, invoice view, and Folio menu entries.

## 2. Before

- Folio backend APIs existed (`api/folios*`).
- Frontend had read-only stay-embedded folio only.
- Menu/nav: Core + Hotel only.

## 3. After

- `useFolioApiClient` for list/show/invoice/charge-to-room/pay/void.
- Pages: `/folios`, `/folios/view/[id]`, `/folios/charge-to-room`.
- Menu + static nav include Folio for front desk / cashier roles.
- Stay folio UI links to full folio page.

## 4. Reason

User asked for frontend of completed modules; pick one module, complete, commit.
Folio is the money hub after Hotel.

## 5. Files Affected

- `frontend/composables/api-clients/useFolioApiClient.ts`
- `frontend/pages/folios/index.vue`
- `frontend/pages/folios/view/[id].vue`
- `frontend/pages/folios/charge-to-room.vue`
- `frontend/utils/app-modules.ts`
- `frontend/components/hotel/StayViewFolio.vue`
- `frontend/pages/hotel/stays/index.vue`
- `backend-laravel/Modules/Core/config/menu.php`
- work kanban 82 + PLAN/SUMMARY

## 6. Verification / Status

- [x] Code-only (no npm/dev server run per coding-agent default)
- [x] UI never edits/deletes charges or payments; void requires reason
- [x] Charge-to-room requires explicit `stay_id` selection
- [ ] Browser E2E deferred
