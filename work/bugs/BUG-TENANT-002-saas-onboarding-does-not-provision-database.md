---
id: BUG-TENANT-002
title: SaaS tenant onboarding leaves email delivery and domain login unconfigured
status: IN_PROGRESS
type: TENANT
severity: high
created: 2026-11-28
---

# BUG-TENANT-002: SaaS tenant onboarding leaves email delivery and domain login unconfigured

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
The SaaS UI created a unique tenant with its database reachable, Core migrations ready, and an active Owner. The detail page reports onboarding `Ready` and records `Complex created`. Email delivery is `not_configured` because the active mail default is `log`. The tenant hostname still does not resolve in Windows, so Owner login through that host cannot begin.

## 6. Evidence
- Browser-created tenant `copilot-qa-20261128-0655` is listed with primary domain `copilot-qa-20261128-0655.mtmp.local`.
- The create response and detail readiness show the tenant database reachable, required Core tables present, and an active Owner account.
- Central activity history records `Complex created`.
- `MAIL_MAILER` is unset in `backend-laravel/.env`; `backend-laravel/config/mail.php` defaults to `log`, and `OwnerCredentialsDeliveryService` returns `not_configured` for non-delivering mailers.
- Browser navigation to the tenant host failed with `ERR_NAME_NOT_RESOLVED`. Writing a hosts alias was denied; no OS hosts file change was made.
- The user's browser screenshot shows the same DNS error for `copilot-qa-20261128-0655.mtmp.local:3000`.
- Windows hosts does not contain `copilot-qa-20261128-0655.mtmp.local`, and DNS lookup fails. The repository-root `.env` needed to point Compose at the Windows hosts file is absent.
- Nexro's local flow mounts the Windows hosts file into the Laravel container via repository-root `HOSTS_FILE_SOURCE`, then runs `hosts:sync` on domain creation. This repository now has the equivalent Compose mount and onboarding sync code, but the local mount has not been configured or applied by recreating the Laravel container.

## 7. Root Cause
The original code omitted physical provisioning and synchronous Owner credential delivery. Provisioning is now implemented and browser-verified. This local runtime has no delivery-capable mailer, so the app correctly reports `not_configured` instead of writing credentials to logs. The new tenant hostname is also absent from the Windows hosts file.

## 8. Workaround
Set `HOSTS_FILE_SOURCE=C:/Windows/System32/drivers/etc/hosts` in the repository-root `.env`, recreate the Laravel service so the mount applies, and confirm the container can write the mounted file. Then run `php artisan hosts:sync` and retry the tenant URL. Configure a delivery-capable mailer separately before expecting an Owner email.

## 9. Suggested Fix
Provisioning and Owner creation are implemented. Local tenant creation now runs `hosts:sync`, and registered tenant domains are merged into Sanctum's stateful hosts. The remaining domain blocker is local setup: the Windows hosts-file bind mount is not configured/applied. After that setup, retest tenant login; configure an approved mail transport separately. Never log temporary credentials or reuse/reset an existing tenant database.

## 10. Impact
New complexes cannot use tenant-host staff flows until a separate database is provisioned. Owners do not receive onboarding credentials by email.

## 11. Related
- Work item: 114
- Change report: `work/changes/2026-11-28--114-auto-tenant-provisioning.md`
- Browser retest: `work/changes/2026-11-28--114-browser-tenant-onboarding.md`
- Nexro-style local domain sync: `work/changes/2026-11-28--114-nexro-style-domain-sync.md`
- DNS retest: `work/changes/2026-11-28--114-nexro-domain-resolution-retest.md`
- Other bugs: none
