# Change Report: SaaS Admin Owner Retry and Onboarding

- **Date**: 2026-11-27
- **By**: Copilot
- **Change ID**: 114

## 1. Summary
Added the central SaaS admin flows and a safe retry path for tenant Owner setup.

## 2. Before
- The SaaS registry API had basic tenant CRUD, but no central-host admin screens, readiness checklist, or lifecycle activity history.
- Owner provisioning used `updateOrCreate`, which could change an existing staff role and password.
- If Owner creation was deferred until after tenant provisioning, the UI had no retry action.
- The static Core navigation fallback did not include Outlets.

## 3. After
- Added central-host SaaS login, navigation, dashboard, tenant listing/create/edit/detail screens, readiness status, and lifecycle history.
- Reviewed Nexro's local SaaS layout, dashboard, tenant list, form, and detail patterns, then adapted the responsive grouped navigation, account menu, activity cards, and two-column form layout without copying billing or subscription scope.
- Added a Super Admin Owner retry endpoint. It creates a new staff record only when the email is unused, returns a conflict for unavailable schema or duplicate email, and restores the previous tenant connection configuration.
- Added recent lifecycle activity to the dashboard, plus an active account menu, dynamic page titles, and a password visibility/return-path flow on login.
- Updated SaaS login guard ordering so the public sign-in page renders before the existing-session probe; the session check now runs after mount and preserves redirect behavior for authenticated users.
- Added an Owner setup form that is enabled only after the tenant database and Core schema are ready. Passwords are not included in responses or activity metadata.
- Added focused API regression tests and Outlets to the static Core menu fallback.
- Tracked the unsafe Owner upsert as `BUG-AUTH-001` and linked it to work item 114.

## 4. Reason
Module A needs a usable central Super Admin workflow and a safe way to finish Owner setup after the approved tenant provisioning process.

## 5. Files Affected
- `backend-laravel/Modules/Saas/` — tenant readiness, lifecycle activity, Owner retry API, dashboard activity, and migration.
- `backend-laravel/tests/Feature/Modules/Saas/SaasRegistryTest.php` and `backend-laravel/tests/Support/TenancyTestCase.php` — focused API coverage and schema support.
- `frontend/pages/saas/`, `frontend/layouts/saas.vue`, `frontend/middleware/saas.global.ts`, `frontend/composables/api-clients/useSaasApiClient.ts`, and `frontend/composables/auth/useSaasAuth.ts` — central admin experience adapted to reviewed Nexro patterns.
- `frontend/pages/saas/tenants/` — error-state handling, URL filters, and Owner retry form.
- `frontend/utils/app-modules.ts` — static Core navigation fallback.
- `work/inprogress/114-nexro-style-saas-super-admin-ui.md`, `work/bugs/BUG-AUTH-001-tenant-owner-email-can-promote-existing-staff.md`, `work/bugs/BUG-APP-011-static-core-fallback-missing-outlets.md`, `work/bugs/BUG-ENV-004-nuxt-saas-routes-not-registered.md`, `work/bugs/BUG-APP-012-saas-login-route-stays-blank.md`, `work/bugs/INDEX.md`, and `work/SUMMARY.md` — tracking.

## 6. Verification / Status
- [x] `git diff --check` completed without whitespace errors.
- [ ] Tests run. Focused API tests are authored but not executed under the active code-only workflow.
- [x] Browser verified. `saas.mtmtp.local:3000/saas/login` renders the Email, Password, and Sign in controls; the unauthenticated `saas/auth/me` request returns the expected 401. The route-registration issue was resolved by restarting only the Nuxt dev container.
- [ ] Tenant create/login flow and central-host authorization still require an authenticated Super Admin browser session.
- [ ] Tenant migrations were not run.
- [ ] Kanban checker could not execute on this Windows checkout because CRLF caused Bash to parse `set -o pipefail` as `pipefail\r`.
- [ ] Module A remains in progress. Formal agreement on the PDF tenancy wording is still required before acceptance.
