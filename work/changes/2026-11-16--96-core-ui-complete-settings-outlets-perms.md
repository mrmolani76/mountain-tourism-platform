# Change Report: Core UI complete — settings, outlets, owner permissions

- **Date**: 2026-11-16
- **By**: Agent
- **Change ID**: 96

## 1. Summary

Core SPA pages now match Laravel Core APIs: real settings form, outlets listing/CRUD, menu entry, and owner `*` permission in route middleware.

## 2. Before

- Settings called `/general-settings/*` with empty form
- No outlets page / menu
- Staff pages required `users.view` but owner perms were only `['*']` → `/unauthorized`

## 3. After

- Settings → `GET|PATCH /api/settings` with currency / timezone / units / tax_rates
- Outlets page + EQMS `outlets/list` routes + Core menu
- Middleware: `*` grants all `checkPermission` meta checks

## 4. Reason

User asked to complete/fix Core UI (code only).

## 5. Files Affected

- `frontend/pages/general-settings.vue`
- `frontend/pages/outlets.vue`
- `frontend/pages/buildings.vue`, `frontend/pages/seasons.vue`
- `frontend/composables/api-clients/useGeneralSettingApiClient.ts`
- `frontend/types/general-setting.ts`
- `frontend/middleware/auth.global.ts`
- `backend-laravel/Modules/Core/routes/api.php`
- `backend-laravel/Modules/Core/config/listing.php`
- `backend-laravel/Modules/Core/config/menu.php`
- `backend-laravel/Modules/Core/App/Http/Controllers/CoreListingController.php`
- work kanban / change report

## 6. Verification / Status

- [ ] Not run (code-only)
- [ ] Notes: Nuxt restart picks up pages; Laravel routes are file-based (no migrate)
