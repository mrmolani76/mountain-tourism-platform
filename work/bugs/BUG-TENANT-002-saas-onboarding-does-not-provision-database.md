---
id: BUG-TENANT-002
title: SaaS onboarding does not provision tenant database or email Owner credentials
status: RETESTING
type: TENANT
severity: high
created: 2026-11-28
---

# BUG-TENANT-002: SaaS onboarding does not provision tenant database or email Owner credentials

## 1. Summary
The prior SaaS onboarding flow only wrote central registry/domain records. Automatic tenant database provisioning and Owner creation now pass a local browser retest; email delivery and tenant-host login remain unverified due local configuration.

## 2. Environment
Local development, Mountain Tourism Laravel + Nuxt SaaS onboarding.

## 3. Steps to Reproduce
1. Sign in to the central SaaS admin.
2. Create a complex and provide an Owner email.
3. Check PostgreSQL for the tenant database and check the Owner email inbox.

## 4. Expected
The onboarding flow creates a new, isolated tenant database, runs configured tenant migrations, creates an Owner with a forced first-login password change, and emails the temporary credentials when a mail transport is configured.

## 5. Actual
The SaaS UI created a unique tenant with its database reachable, Core migrations ready, and an active Owner. The detail page reports onboarding `Ready` and records `Complex created`. The email delivery status is `not_configured` because the active mail default is `log`. The tenant hostname does not resolve in this Windows environment, so Owner login through that host could not be completed.

## 6. Evidence
- Browser-created tenant `copilot-qa-20261128-0655` is listed with primary domain `copilot-qa-20261128-0655.mtmp.local`.
- The create response and detail readiness show the tenant database reachable, required Core tables present, and an active Owner account.
- Central activity history records `Complex created`.
- `MAIL_MAILER` is unset in `backend-laravel/.env`; `backend-laravel/config/mail.php` defaults to `log`, and `OwnerCredentialsDeliveryService` returns `not_configured` for non-delivering mailers.
- Browser navigation to the tenant host failed with `ERR_NAME_NOT_RESOLVED`. Writing a hosts alias was denied; no OS hosts file change was made.

## 7. Root Cause
The original code omitted physical provisioning and synchronous Owner credential delivery. Provisioning is now implemented and browser-verified. This local runtime has no delivery-capable mailer, so the app correctly reports `not_configured` instead of writing credentials to logs. The new tenant hostname is also absent from the Windows hosts file.

## 8. Workaround
Configure a delivery-capable mailer before expecting an Owner email. Add the tenant host to local DNS/hosts with appropriate permissions before browser-testing that tenant domain.

## 9. Suggested Fix
Provisioning and Owner creation are implemented. Configure the approved local mail transport and tenant hostname resolution, then retest email delivery and Owner login. Never log temporary credentials or reuse/reset an existing tenant database.

## 10. Impact
New complexes cannot use tenant-host staff flows until a separate database is provisioned. Owners do not receive onboarding credentials by email.

## 11. Related
- Work item: 114
- Change report: `work/changes/2026-11-28--114-auto-tenant-provisioning.md`
- Browser retest: `work/changes/2026-11-28--114-browser-tenant-onboarding.md`
- Other bugs: none
