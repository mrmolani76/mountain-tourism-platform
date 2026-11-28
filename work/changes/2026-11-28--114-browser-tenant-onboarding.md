# Change Report: Browser Tenant Onboarding Retest

- **Date**: 2026-11-28
- **By**: Copilot
- **Change ID**: 114 / BUG-TENANT-002

## 1. Summary
Created and reviewed one unique local tenant through the SaaS admin UI.

## 2. Before
The SaaS admin login was browser-verified, but tenant creation, per-tenant database readiness, Owner setup, and lifecycle activity were pending browser acceptance.

## 3. After
Created `copilot-qa-20261128-0655` with primary domain `copilot-qa-20261128-0655.mtmp.local`. The create response and tenant detail page reported a reachable tenant database, required Core migrations present, an active Owner, and overall onboarding readiness `Ready`. Central activity history recorded `Complex created`.

The email delivery status was `not_configured`. `MAIL_MAILER` is unset and Laravel defaults to the non-delivering `log` mailer. Browser navigation to the tenant host failed with `ERR_NAME_NOT_RESOLVED`; the Windows hosts-file update was denied by permissions, and no OS hosts file was changed.

## 4. Reason
The user requested an end-to-end check of SaaS tenant creation, separate database/domain setup, Owner login, and lifecycle records without resetting existing tenant databases.

## 5. Files Affected
- `work/inprogress/114-nexro-style-saas-super-admin-ui.md`
- `work/bugs/BUG-TENANT-002-saas-onboarding-does-not-provision-database.md`
- `work/bugs/INDEX.md`
- `work/SUMMARY.md`
- Local central SaaS registry and lifecycle activity: created one test tenant.
- Local tenant database: created and migrated for `copilot-qa-20261128-0655`.

## 6. Verification / Status
- [x] Browser-created tenant appears in the SaaS tenant list with the expected primary domain.
- [x] Tenant detail reports database reachable, Core migrations ready, active Owner, and onboarding `Ready`.
- [x] Central lifecycle activity shows `Complex created`.
- [ ] Owner login through the tenant hostname; blocked because the hostname does not resolve locally.
- [ ] Owner credential email; blocked because the configured mailer defaults to `log` and reports `not_configured`.
- [x] No existing tenant/database was reset or modified; no credentials were written to reports.
- [ ] Automated tests/build not run; this pass was an explicitly requested browser functional check.
