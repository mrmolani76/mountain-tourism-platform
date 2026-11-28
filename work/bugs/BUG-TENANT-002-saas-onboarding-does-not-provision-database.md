---
id: BUG-TENANT-002
title: SaaS onboarding does not provision tenant database or email Owner credentials
status: IN_PROGRESS
type: TENANT
severity: high
created: 2026-11-28
---

# BUG-TENANT-002: SaaS onboarding does not provision tenant database or email Owner credentials

## 1. Summary
Creating a complex from the SaaS UI writes its central registry and domain but does not create and migrate its dedicated tenant database or email the initial Owner credentials.

## 2. Environment
Local development, Mountain Tourism Laravel + Nuxt SaaS onboarding.

## 3. Steps to Reproduce
1. Sign in to the central SaaS admin.
2. Create a complex and provide an Owner email.
3. Check PostgreSQL for the tenant database and check the Owner email inbox.

## 4. Expected
The onboarding flow creates a new, isolated tenant database, runs configured tenant migrations, creates an Owner with a forced first-login password change, and emails the temporary credentials when a mail transport is configured.

## 5. Actual
The central tenant/domain rows are created, but the service does not create the physical database or run tenant migrations. Owner creation can be skipped when its schema is unavailable. The UI states that the temporary password is not emailed.

## 6. Evidence
- `TenantService::register()` records the tenant with `db_name = <slug>_local` but does not provision a database.
- The SaaS activity event for the reported creation records `owner_created=false` and `temporary_password_generated=false`.
- The local Laravel mail default is `log`, with no SMTP username or password configured. A log mailer must not receive temporary credentials because that writes message contents to logs.

## 7. Root Cause
Physical provisioning is explicitly left to `tenants:provision-local`/operations, rather than being part of the central SaaS onboarding service. The create form and API treat Owner credentials as optional and do not send a notification. In the current local runtime, the configured mailer is `log`, so real email delivery is unavailable.

## 8. Workaround
Provision the tenant database and run tenant migrations separately, then use the tenant detail page to create an Owner. This does not deliver credentials by email.

## 9. Suggested Fix
Create only a new database after confirming the name is unused, run all configured tenant migrations, and roll back only resources created by the failed attempt. Create the initial Owner after Core migrations. Send credentials synchronously through an explicitly configured mail transport, and report mail delivery status without logging credentials. Keep each tenant on its own database.

## 10. Impact
New complexes cannot use tenant-host staff flows until a separate database is provisioned. Owners do not receive onboarding credentials by email.

## 11. Related
- Work item: 114
- Change report: `work/changes/2026-11-28--114-auto-tenant-provisioning.md`
- Other bugs: none
