---
title: Build Nexro-style SaaS super-admin UI and tenant onboarding
id: 114
priority: high
status: todo
milestone: 1
module: A-core
area: backend/frontend
effort: L
dependencies: none
bug: none
created: 2026-11-26
updated: 2026-11-26
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
Authorized super admins can securely manage complex tenants from the central host, with a clear onboarding/readiness flow that does not imply a tenant is ready before its database and owner setup are complete.

### Phase A — MVP SaaS Admin (required)
- Dedicated central-host login/session guard and SaaS layout, separate from tenant staff navigation.
- Dashboard with real central-API counts for total, active, and inactive tenants, plus a clear action to add a tenant. Do not show guessed or hard-coded metrics.
- Tenant list using the repository's existing EQMS `DataTable` pattern: server pagination, search, active/inactive filter, status chips, loading/empty/error states, and shareable URL filter state.
- Create tenant flow for complex name, slug/domain, and optional initial Owner credentials where supported. Show the API's `owner_created` result clearly; never re-display or log a submitted password.
- Tenant detail/edit screen showing safe registry and domain information, with explicit activate/deactivate confirmation and refreshed status.
- Show actionable onboarding readiness for central registry/domain, tenant database/migrations, and Owner account. Where the current API cannot safely report readiness, add a central SaaS API status endpoint rather than probing tenant databases from the browser.
- Keep server-side `saas.auth` authorization authoritative. Tenant staff must not reach the central UI or APIs.

### Phase B — Nexro-inspired usability upgrades (in scope)
- Grouped, responsive SaaS navigation and consistent page titles, card spacing, status chips, and action placement.
- Clear first-load skeletons, retryable failures, empty states, validation messages, and disabled/double-submit protection.
- Keep list filters in the URL, provide clear-filters behavior, and deep-link from dashboard cards to the matching tenant list.
- Add a compact tenant lifecycle activity history for create/activate/deactivate, recording actor and timestamp on the central side.
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
- [ ] Surface tenant database/migration and Owner readiness accurately; an incomplete owner setup must not appear successful.
- [ ] Add central lifecycle activity records for tenant creation and activation changes; do not leak credentials or exception details.
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
- Adapt the structure and interaction pattern from the local read-only Nexro reference: `layouts/saas.vue`, `pages/saas/dashboard.vue`, and `pages/saas/tenants/{index,form,view}`. Do not copy Nexro subscription/billing scope.
- Existing backend limitation to resolve safely: physical tenant DB provisioning is not performed by `TenantService::register()`. Coordinate the UI's readiness state with the project's approved provisioning flow.
- See [MVP scope audit](../../docs/MVP_SCOPE_AUDIT.md).

---

## Return to Pending Log (when work stops midway)
- **Returned by**: Copilot
- **Date**: 2026-11-26
- **Reason**: Refined the TODO against Nexro's SaaS admin pattern and added Mountain Tourism MVP-specific onboarding/readiness improvements. The user requested planning only; implementation remains pending.
- **Next step**: Pick up this TODO when implementation is requested. Deliver central-host UI and only the backend support needed for accurate onboarding readiness.
- **Change report**: `work/changes/2026-11-26--114-nexro-saas-admin-ui-todo.md`

---

## Completion Log (when work is complete)

- **Completed by**:
- **Date**:
- **What was done**:
- **Files changed**:
- **Verification**:
- **Change report**:
- **PLAN / SUMMARY**:
