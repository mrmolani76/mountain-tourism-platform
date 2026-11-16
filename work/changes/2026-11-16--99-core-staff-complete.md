# Change Report: Complete Core Staff + listing config fix

- **Date**: 2026-11-16
- **By**: Agent
- **Change ID**: 99
- **Bug**: BUG-API-001

## 1. Summary

Finished Core Staff (outlet access on form/view, reset envelope) and fixed listing config shadow that returned 404 Unknown listing resource.

## 2. Before

- App `config/listing.php` buildings stub overwrote Core module config → no `model` → 404.
- Staff create/edit had no outlet access UI; reset API returned `{detail}` only.
- Staff email search used Postgres-only `ilike`.

## 3. After

- App listing config empty; CoreListingController falls back to module file.
- Staff create/update accepts `outlet_ids`; formInitialData returns outlets; form/view wired.
- Reset password returns `status: true`; search uses `like`.

## 4. Reason

User: complete Core Staff; Core listings 404 on list/config.

## 5. Files Affected

- `backend-laravel/config/listing.php`
- `Modules/Core/App/Http/Controllers/CoreListingController.php`
- `Modules/Core/App/Http/Controllers/UserListingController.php`
- `Modules/Core/App/Http/Controllers/AuthController.php`
- `Modules/Core/App/Http/Requests/StoreStaffRequest.php`
- `Modules/Core/App/Http/Requests/UpdateStaffRequest.php`
- `frontend/pages/users/form/[[id]].vue`
- `frontend/pages/users/index.vue`
- `frontend/pages/users/view/[id].vue`
- `frontend/components/UserViewInfo.vue`
- `frontend/composables/api-clients/useUserApiClient.ts`
- `frontend/composables/api-clients/useSharedApiClient.ts`
- work kanban / bug / change report

## 6. Verification / Status

- [ ] Code-only (no run)
- [ ] BUG-API-001 fixed in code; browser retest pending
