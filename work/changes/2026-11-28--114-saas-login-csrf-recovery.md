# Change Report: SaaS Login CSRF Recovery

- **Date**: 2026-11-28
- **By**: Copilot
- **Change ID**: 114 / BUG-AUTH-002

## 1. Summary
Added Nexro-style stale CSRF recovery to the shared Nuxt API client.

## 2. Before
`frontend/composables/core/useApi.ts` used a 30-second timeout, skipped the CSRF bootstrap when any `XSRF-TOKEN` cookie existed, and handled HTTP 419 as a normal request error. Local logs showed CSRF bootstrap taking about 39 seconds and login about 34 seconds, so both could exceed the frontend timeout.

## 3. After
The API client uses a bounded 60-second timeout, stops mutations if CSRF bootstrap fails, and clears the host-only XSRF cookie after HTTP 419. It fetches `/sanctum/csrf-cookie`, reads the fresh cookie, and retries the rejected mutating request once. Concurrent 419 responses share one recovery request.

## 4. Reason
The SaaS login was reporting `CSRF token mismatch`. The Nexro reference uses the same clear-cookie, refresh-cookie recovery pattern.

## 5. Files Affected
- `frontend/composables/core/useApi.ts`
- `work/bugs/BUG-AUTH-002-saas-login-csrf-token-mismatch.md`
- `work/bugs/INDEX.md`
- `work/inprogress/114-nexro-style-saas-super-admin-ui.md`
- `work/SUMMARY.md`

## 6. Verification / Status
- [x] Source diff reviewed.
- [x] Browser login redirected to `/saas`; account menu confirmed the platform admin.
- [x] A later separate browser flow verified tenant creation, database/Core readiness, Owner creation, and lifecycle activity; see `2026-11-28--114-browser-tenant-onboarding.md`.
- [ ] Tenant-domain login and email delivery remain pending because the tenant hostname does not resolve locally and the mailer is `not_configured`.
- [ ] Automated tests/build not run under the code-only workflow.
- [x] No tenant database was changed during this CSRF-only verification; the separate onboarding test created one unique tenant.
