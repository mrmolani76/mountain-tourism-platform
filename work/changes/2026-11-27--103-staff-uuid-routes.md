# Change Report: Staff UUID Routes

- **Date**: 2026-11-27
- **By**: Copilot
- **Change ID**: 103-staff-uuid-routes

## 1. Summary
Updated Staff view/edit URLs and API lookups to use persisted UUIDs, following the Nexro pattern.

## 2. Before
- `core_staff` had no UUID column.
- Staff APIs serialized the numeric primary key as `uuid`; the screenshot showed `/users/view/4`.
- Nuxt view/edit pages used a route parameter named `id`, and the form fetch called `/user/fetch?id=...`.

## 3. After
- Added a tenant migration to backfill and uniquely index Staff UUIDs.
- New Staff receive UUIDs on model creation.
- Auth, listing, detail, and resource responses include real UUIDs; listing links and lookup/update routes resolve UUIDs. Numeric lookup remains as a legacy compatibility fallback.
- Nuxt Staff pages use `uuid` route params; fetch/update requests use UUIDs. Internal numeric IDs remain for database foreign keys and password-reset request payloads.
- Added API regression cases for auth UUID payloads, UUID lookup endpoints, listing links, and resource routes.

## 4. Reason
The user requested UUID identifiers in Staff URLs and asked to follow Nexro's established listing pattern (`item.uuid` in view/edit links, UUID-based detail fetch).

## 5. Files Affected
- `backend-laravel/Modules/Core/App/Models/Staff.php`
- `backend-laravel/Modules/Core/App/Http/Controllers/AuthController.php`
- `backend-laravel/Modules/Core/App/Http/Controllers/StaffController.php`
- `backend-laravel/Modules/Core/App/Http/Controllers/UserListingController.php`
- `backend-laravel/Modules/Core/App/Http/Requests/UpdateStaffRequest.php`
- `backend-laravel/Modules/Core/Database/migrations/tenant/2026_11_27_000003_add_uuid_to_core_staff.php`
- `backend-laravel/tests/Feature/Modules/Core/AuthApiTest.php`
- `backend-laravel/tests/Feature/Modules/Core/TenantIsolationTest.php`
- `backend-laravel/tests/Support/TenancyTestCase.php`
- `frontend/composables/api-clients/useUserApiClient.ts`
- `frontend/composables/auth/useAuthService.ts`
- `frontend/pages/users/form/[[uuid]].vue`
- `frontend/pages/users/view/[uuid].vue`
- `frontend/types/user.ts`
- `work/bugs/BUG-APP-008-staff-routes-use-numeric-ids.md`
- `work/bugs/INDEX.md`
- `work/inprogress/103-manual-browser-acceptance-checks.md`
- `work/PLAN.md`
- `work/SUMMARY.md`
- `work/MEMORY.md`

## 6. Verification / Status
- [x] Frontend Nuxt dev server is running on port 3000 with source-mounted HMR. Browser login renders at `/login`; API health and proxy returned HTTP 200 during preflight.
- [x] Nexro reference checked: `/users/index.vue` routes from `item.uuid`; `/user/show?uuid=...` fetches the detail record.
- [x] `git diff --check` passed.
- [x] Work kanban validation passed.
- [ ] PHPUnit not run under the code-only workflow.
- [x] After approval, applied only `2026_11_27_000003_add_uuid_to_core_staff.php` to `lab_local`. Verification confirmed 8 of 8 Staff rows have non-null, distinct UUIDs and the migration is recorded.
- [x] Browser check passed: signed into the local test tenant, Staff listing links contained UUIDs, and both UUID view and edit routes loaded the selected Staff record. No Staff form was saved.
