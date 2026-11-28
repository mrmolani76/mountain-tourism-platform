---
title: Build Nexro-style SaaS super-admin UI and tenant onboarding
id: 114
priority: high
status: inprogress
milestone: 1
module: A-core
area: backend/frontend
effort: L
dependencies: none
bug: BUG-TENANT-002
created: 2026-11-26
updated: 2026-11-28
---

# Build Nexro-style SaaS Super-admin UI and Tenant Onboarding

## What to do (What)
Build a central-host-only SaaS super-admin experience for Module A. Follow Nexro's clear SaaS shell, dashboard, tenant list, create/edit form, and tenant detail flow, adapted to this project's DB-per-tenant model and MVP scope.

## Current / Before
- The Laravel SaaS API exposes tenant list, create, detail, update, activate, and deactivate endpoints.
- `TenantService::register()` creates the central tenant registry and primary domain. Tenant database provisioning remains an operational step; owner creation can return `owner_created: false` when the tenant database or `core_staff` table is not ready.
- No central SaaS admin pages, dashboard, layout, or tenant onboarding UI were found in the Nuxt frontend.
- Nexro reference is a separate SaaS layout with grouped navigation, overview cards, server-side tenant listing with filters, and separate tenant create/edit/detail screens.

## Expected / After
Authorized super admins can securely manage complex tenants from the central host. Creating a tenant provisions a new, separate database and runs configured tenant migrations before creating an initial Owner when an Owner email is supplied. The Owner receives temporary credentials by email when a delivery-capable mail transport is configured; mail status is reported explicitly.

### Phase A — MVP SaaS Admin (required)
- Dedicated central-host login/session guard and SaaS layout, separate from tenant staff navigation.
- Dashboard with real central-API counts for total, active, and inactive tenants, plus a clear action to add a tenant. Do not show guessed or hard-coded metrics.
- Tenant list using the repository's existing EQMS `DataTable` pattern: server pagination, search, active/inactive filter, status chips, loading/empty/error states, and shareable URL filter state.
- Create tenant flow for complex name, slug/domain, and optional initial Owner email. Create only a new, unused tenant database, run all configured tenant migrations, and then create the Owner. Show database/migration, `owner_created`, and email delivery results clearly; never log a password.
- Tenant detail/edit screen showing safe registry and domain information, with explicit activate/deactivate confirmation and refreshed status.
- Show actionable onboarding readiness for central registry/domain, tenant database/migrations, and Owner account. Where the current API cannot safely report readiness, add a central SaaS API status endpoint rather than probing tenant databases from the browser.
- Allow the Super Admin to retry Owner setup once the tenant database and Core schema are ready, without changing an existing staff account.
- Keep server-side `saas.auth` authorization authoritative. Tenant staff must not reach the central UI or APIs.

### Phase B — Nexro-inspired usability upgrades (in scope)
- Grouped, responsive SaaS navigation and consistent page titles, card spacing, status chips, and action placement.
- Clear first-load skeletons, retryable failures, empty states, validation messages, and disabled/double-submit protection.
- Keep list filters in the URL, provide clear-filters behavior, and deep-link from dashboard cards to the matching tenant list.
- Add a compact tenant lifecycle activity history for create/profile-update/activate/deactivate, recording actor and timestamp on the central side.
- Add a guided readiness checklist with safe next-step instructions when provisioning or owner setup is incomplete. Never expose DB credentials or raw exception details.
- Use confirmation for lifecycle actions, and do not provide tenant impersonation or direct cross-tenant database access.

### Explicit scope boundary
- Do not add subscriptions, plans, billing, payment processing, self-signup, email invitations, or other Nexro SaaS modules. They are not part of this project's current MVP scope.
- Use existing Laravel + Nuxt/Vuetify patterns and dependencies. No new packages or infrastructure setup.

## Why
Module A includes super-admin complex and Owner operations. The backend has the start of this API, but the project has no usable central-host SaaS UI or safe end-to-end onboarding experience. Nexro is the UI-pattern reference, while the MVP scope remains the feature boundary.

## Acceptance Criteria (complete only when all ticked)
- [ ] Confirm central-host route and SaaS session behavior against the existing tenancy/auth architecture.
- [ ] Add the dedicated SaaS layout/navigation, dashboard, tenant list, create/edit form, and tenant detail route.
- [ ] Use server-side pagination/search/filtering through the existing listing conventions; preserve list filters in the URL.
- [ ] Complete create/list/detail/edit/activate/deactivate flows with explicit loading, empty, validation, error, and confirmation states.
- [ ] Automatically create a new, unused database for each tenant, run all configured tenant migrations, and roll back only resources created by a failed provisioning attempt.
- [ ] Create the initial Owner only after migrations succeed, enforce first-login password reset, and email temporary credentials through a configured transport without logging them.
- [ ] Surface tenant database/migration, Owner, and email delivery readiness accurately; an incomplete setup must not appear successful, and provide a safe Owner retry when ready.
- [ ] Add central database-backed lifecycle activity records for tenant creation, profile updates, and activation changes; do not leak credentials or exception details.
- [ ] Add focused API/UI coverage proving central authorization, tenant-staff denial, lifecycle behavior, and partial owner provisioning handling.
- [ ] Browser-check the central-host flows at desktop and mobile widths; verify tenant-host access is denied.
- [ ] Confirm no subscriptions, billing, payments, self-signup, impersonation, or unrequested setup work was added.
- [ ] `work/changes/YYYY-MM-DD--114-<desc>.md` written.
- [ ] PLAN.md + SUMMARY.md updated.

## Files Likely Affected
- `frontend/layouts/`
- `frontend/pages/saas/`
- `frontend/composables/api-clients/`
- `backend-laravel/Modules/Saas/`
- `backend-laravel/tests/Feature/Modules/Saas/`

## Notes / Follow-ups
- Server authorization remains authoritative; hiding links is not sufficient.
- 2026-11-27: Added safe Owner retry after tenant provisioning and a conflict response when Core schema is unavailable or the email is already assigned. `BUG-AUTH-001` tracks the prior unsafe upsert.
- 2026-11-27: Reviewed the local Nexro layout, dashboard, tenant list, create/edit form, and detail page. Adapted its responsive grouped sidebar, account menu, KPI/activity dashboard, and two-column form cards; excluded billing, subscriptions, signups, and other out-of-scope modules.
- 2026-11-27: Restarted only the Nuxt dev container; the generated route table now includes `/saas/login`. Fixed the blank login screen by handling the public route before session bootstrap and moving the session probe after mount; browser now shows the sign-in form at `saas.mtmtp.local:3000`. `BUG-APP-012` is verified. Tenant create/login acceptance is waiting for an authenticated Super Admin session. Formal tenancy contract amendment remains required for Module A acceptance.
- 2026-11-27: Kanban validation was attempted but the checker script failed on CRLF (`pipefail\r`) in this Windows checkout.
- 2026-11-27: Resumed for the requested browser onboarding check. Use only a temporary local SaaS admin and uniquely named test tenant; never overwrite existing accounts or reset an existing tenant database.
- 2026-11-28: The user requested code first, then a full local central + tenant database reset, and will perform browser testing. Central `mountain_tourism` and registered tenant `lab_local` were reset and provisioned with local fixtures; the system `postgres` database was preserved. No browser or automated tests were run in this step.
- 2026-11-28: User requested matching Nexro's initial Owner temporary-password/onboarding behavior. Inspect the Nexro implementation and align this app's flow securely.
- 2026-11-28: User clarified that every tenant must have a separate database and requested Nexro-style automatic database provisioning and temporary-credential email. `BUG-TENANT-002` tracks the confirmed onboarding gap. The local app mailer currently defaults to `log`, so this code must not log credential emails; delivery requires a configured mail transport.
- 2026-11-28: Implemented automatic new PostgreSQL tenant DB creation, configured tenant migrations, Core schema verification, Owner creation with forced first-login reset, and synchronous credential-email status handling. Existing DBs are refused and never reset. The local `log` mailer is reported as `not_configured`; no test tenant, migration, build, or browser flow was run. See `work/changes/2026-11-28--114-auto-tenant-provisioning.md`.
- 2026-11-28: Central `saas_tenant_activity` is the DB-backed SaaS lifecycle log; tenant staff security events remain in tenant-local `core_audit_event`. Do not add generic request-payload logging to the central DB because it can capture temporary passwords and violate tenant separation. Actual tenant-name changes now create a central activity event.
- 2026-11-28: Browser check reproduced a stale Nuxt route import for removed `[id]/edit.vue`; source uses `[uuid]/edit.vue`. Restarted `mtmtp_spa_dev`; the route module now imports `[uuid]/edit.vue`, the SaaS login form renders, and `/saas/tenants` redirects unauthenticated users to login. `BUG-ENV-004` is VERIFIED. Full tenant-list acceptance needs a Super Admin session.
- 2026-11-28: Applied `2026_11_28_055050_add_uuid_to_saas_tenant` to local central database `mountain_tourism`; UUID is non-null, unique, and populated for the existing tenant. Browser dashboard now shows 1 total, 1 active, 0 inactive; `BUG-DATA-001` is VERIFIED. An old tenant-list tab still has a separate Nuxt dynamic-import failure, so full tenant-list acceptance remains pending.
- 2026-11-28: Added Nexro-style recovery for `BUG-AUTH-002`: clear a stale XSRF cookie, fetch a fresh Sanctum CSRF cookie, and retry one rejected mutation once. Initial browser retest was blocked by local Nuxt/Laravel timeouts; the later login verification and tenant-onboarding blocker are recorded below and in `work/changes/2026-11-28--114-saas-login-csrf-recovery.md`.
- 2026-11-28: Browser login now succeeds after increasing the API timeout to 60 seconds and retaining one-time 419 recovery; redirect to `/saas` and the account menu confirmed the platform admin.
- 2026-11-28: Created local test tenant `copilot-qa-20261128-0655` through the SaaS UI at `copilot-qa-20261128-0655.mtmp.local`. The create response and detail page report the tenant database reachable, Core migrations ready, active Owner present, and overall onboarding `Ready`; the central activity panel records `Complex created`. Email status is `not_configured` because the default mailer is `log`. Browser tenant login is blocked because this host does not resolve; adding its local hosts alias was denied by Windows permissions, and no system hosts file was changed. No existing tenant/database was reset or modified. See `work/changes/2026-11-28--114-browser-tenant-onboarding.md`.
- 2026-11-28: Added Nexro-style local tenant-domain sync to SaaS onboarding: on successful tenant provisioning, `hosts:sync` runs and the API/UI reports `synced`, `permission_required`, `failed`, or `not_applicable`. Laravel merges registered tenant hosts into Sanctum stateful domains so new tenant hosts can use cookie auth without a static allowlist. Dev Compose can mount the Windows hosts file using repository-root `HOSTS_FILE_SOURCE`; no Docker commands or OS hosts edits were run. See `work/changes/2026-11-28--114-nexro-style-domain-sync.md`.
- 2026-11-28: User screenshot reproduced `ERR_NAME_NOT_RESOLVED` for `copilot-qa-20261128-0655.mtmp.local`. Compared Nexro: its repo-root `.env` points `HOSTS_FILE_SOURCE` to the Windows hosts file, which Compose mounts at `/etc/hosts`; this checkout has no root `.env` and the hostname is absent from Windows hosts. The Compose/code support is present but must be configured and the Laravel container recreated before the browser URL can resolve. BUG-TENANT-002 returned to `IN_PROGRESS`; see `work/changes/2026-11-28--114-nexro-domain-resolution-retest.md`.
- Fix report: `work/changes/2026-11-28--114-fix-saas-tenant-uuid.md` (central migration applied; dashboard browser verification passed).
- Progress report: `work/changes/2026-11-27--114-saas-owner-retry-and-admin.md` (verification pending).
- Central activity report: `work/changes/2026-11-28--114-central-saas-db-activity.md` (API tests authored, not run; browser acceptance pending).
- UI issue report: `work/changes/2026-11-28--114-stale-nuxt-route-import.md` (route import and login rendering verified; tenant list session pending).
- Central schema issue report: `work/changes/2026-11-28--114-central-saas-uuid-schema-mismatch.md` (cause confirmed; migration/reset pending).
- Reset report: `work/changes/2026-11-28--114-local-central-tenant-db-reset.md`.
- Adapt the structure and interaction pattern from the local read-only Nexro reference: `layouts/saas.vue`, `pages/saas/dashboard.vue`, and `pages/saas/tenants/{index,form,view}`. Do not copy Nexro subscription/billing scope.
- Existing backend limitation to resolve safely: physical tenant DB provisioning is not performed by `TenantService::register()`. Implement central-host-only provisioning that never adopts or resets an existing database; coordinate readiness and email status with the UI.
- See [MVP scope audit](../../docs/MVP_SCOPE_AUDIT.md).

---

## Return to Pending Log (when work stops midway)
- **Returned by**: Copilot
- **Date**: 2026-11-26
- **Reason**: Refined the TODO against Nexro's SaaS admin pattern and added Mountain Tourism MVP-specific onboarding/readiness improvements. The user requested planning only; implementation remains pending.
- **Next step**: Pick up this TODO when implementation is requested. Deliver central-host UI and only the backend support needed for accurate onboarding readiness.
- **Change report**: `work/changes/2026-11-26--114-nexro-saas-admin-ui-todo.md`
- **Returned by**: Copilot
- **Date**: 2026-11-27
- **Reason**: Browser login screen is verified, but no authenticated Super Admin session is available. The user was unavailable to sign in; credentials will not be guessed or created to bypass the real auth flow.
- **Next step**: Resume after the user signs into `http://saas.mtmtp.local:3000/saas/login`; create the requested test tenant and verify its Owner login through the browser.
- **Change report**: `work/changes/2026-11-27--114-saas-owner-retry-and-admin.md`
- **Returned by**: Copilot
- **Date**: 2026-11-28
- **Reason**: SaaS code is present and the user requested to run browser testing themselves. The project central and lab tenant databases were reset and reprovisioned after explicit approval.
- **Next step**: User performs browser acceptance for SaaS admin and tenant Owner login, then resume item 114 for any fixes and final acceptance.
- **Change report**: `work/changes/2026-11-28--114-local-central-tenant-db-reset.md`

---

## Completion Log (when work is complete)

- **Completed by**:
- **Date**:
- **What was done**:
- **Files changed**:
- **Verification**:
- **Change report**:
- **PLAN / SUMMARY**:
