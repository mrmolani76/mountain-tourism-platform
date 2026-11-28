---
id: BUG-AUTH-002
title: SaaS login fails when the CSRF token is stale
status: VERIFIED
type: AUTH
severity: high
created: 2026-11-28
---

# BUG-AUTH-002: SaaS login fails when the CSRF token is stale

## 1. Summary
SaaS login failed with a CSRF mismatch while the local CSRF bootstrap and login requests were exceeding the frontend's 30-second request timeout.

## 2. Environment
Local development, SaaS admin at `saas.mtmtp.local:3000`.

## 3. Steps to Reproduce
1. Open the SaaS admin login page with an existing or stale `XSRF-TOKEN` cookie.
2. Submit valid SaaS admin credentials.
3. Observe the CSRF token mismatch response and failed login.

## 4. Expected
The API client allows the observed local request duration, refreshes a stale CSRF token once after HTTP 419, and reports a clear error if CSRF bootstrap fails.

## 5. Actual
Before the fix, the frontend used a 30-second timeout and reused any existing `XSRF-TOKEN` cookie without recovering from HTTP 419. The login form then reported a generic sign-in failure.

## 6. Evidence
- User reports the login response is `CSRF token mismatch`.
- Laravel request logs showed `/sanctum/csrf-cookie` taking about 39 seconds and `/api/saas/auth/login` taking about 34 seconds, both longer than the old 30-second frontend timeout.
- `frontend/composables/core/useApi.ts` skipped `/sanctum/csrf-cookie` whenever an `XSRF-TOKEN` cookie existed and had no HTTP 419 recovery branch.
- The Nexro reference in `../nexro/laravel-spa-with-saas/utils/csrf.ts` clears stale XSRF cookies and force-fetches a new CSRF cookie; its API client invokes that recovery on HTTP 419.
- After the fix, browser login succeeded and redirected to `/saas`; the account menu showed the platform admin identity.

## 7. Root Cause
The API client's 30-second timeout was shorter than the observed local CSRF and login request durations. It also lacked Nexro-style recovery when a stale token returned HTTP 419.

## 8. Workaround
No manual cookie cleanup is needed after the fix. Tenant provisioning still needs a responsive local frontend and Laravel API.

## 9. Suggested Fix
Implemented in `frontend/composables/core/useApi.ts`: use a bounded 60-second timeout, stop mutations when CSRF bootstrap fails, and refresh/retry once after HTTP 419.

## 10. Impact
SaaS administrators may be unable to sign in or perform other mutating actions while a stale CSRF cookie remains.

## 11. Related
- Work item: 114
- Change report: `../changes/2026-11-28--114-saas-login-csrf-recovery.md`
- Other bugs: none

## 12. Verification
- **Status**: VERIFIED, 2026-11-28.
- Browser login completed successfully; redirected to `/saas` and the account menu confirmed the platform admin.
- Tenant onboarding was not part of this CSRF-only retest. A later separate browser flow created a test tenant and verified database/migration/Owner readiness; tenant-domain login and email delivery remain pending due local hostname and mailer configuration. No tenant database was changed during the CSRF-only verification.
