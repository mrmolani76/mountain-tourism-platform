# Change Report: Automatic SaaS Tenant Provisioning and Owner Email

- **Date**: 2026-11-28
- **By**: Copilot
- **Change ID**: 114

## 1. Summary
Added automatic per-tenant PostgreSQL database creation, tenant migrations, initial Owner setup, and safe temporary-credential email status reporting to SaaS onboarding.

## 2. Before
- `TenantService::register()` created only central tenant and domain rows, using `<slug>_local` as the registry database name.
- Tenant databases and migrations had to be provisioned separately.
- Owner credentials were only shown in the UI and were never emailed.
- The create page said database provisioning was an operations step and email was unavailable.

## 3. After
- SaaS registration first checks PostgreSQL for the generated tenant database name and refuses to reuse an existing database.
- A new database is created and all configured tenant migration paths are run. The required Core schema is checked before the tenant can be activated.
- Only a database created by the current attempt is eligible for rollback after provisioning failure.
- An initial Owner is created after migration, with a hashed temporary password and forced first-login reset.
- Owner credentials are sent synchronously when a delivery-capable mailer is configured. Log/array mailers and failover configurations that could log the message are refused; delivery status is returned and recorded without storing credentials.
- SaaS create and Owner-retry responses and UI show database, migration, and email delivery status.
- Tenant slug length is capped at 57 characters so the generated `<slug>_local` database name remains within PostgreSQL's 63-byte identifier limit.

## 4. Reason
The user requested Nexro-style SaaS onboarding and confirmed each tenant must have its own database. The previous flow left DB creation and migration to operations, so a newly registered tenant was not independently ready and its Owner did not receive credentials by email.

## 5. Files Affected
- `backend-laravel/Modules/Saas/App/Exceptions/TenantDatabaseAlreadyExistsException.php`
- `backend-laravel/Modules/Saas/App/Exceptions/TenantDatabaseProvisioningException.php`
- `backend-laravel/Modules/Saas/App/Http/Controllers/TenantController.php`
- `backend-laravel/Modules/Saas/App/Notifications/OwnerCredentialsNotification.php`
- `backend-laravel/Modules/Saas/App/Services/OwnerCredentialsDeliveryService.php`
- `backend-laravel/Modules/Saas/App/Services/TenantDatabaseProvisioner.php`
- `backend-laravel/Modules/Saas/App/Services/TenantService.php`
- `frontend/composables/api-clients/useSaasApiClient.ts`
- `frontend/pages/saas/tenants/create.vue`
- `frontend/pages/saas/tenants/[uuid]/index.vue`
- `work/bugs/BUG-TENANT-002-saas-onboarding-does-not-provision-database.md`
- `work/bugs/INDEX.md`
- `work/inprogress/114-nexro-style-saas-super-admin-ui.md`
- `work/SUMMARY.md`

## 6. Verification / Status
- [ ] Tests, builds, linters, migrations, and browser acceptance were not run under the active code-only workflow; the user will perform browser testing.
- [x] Safely inspected local mail configuration: default mailer is `log`; SMTP username/password are not configured. No credentials were printed or changed.
- [x] Reviewed the implementation for database-name validation, existing-database refusal, tenant connection restoration, migration-failure cleanup, and absence of temporary passwords from central activity metadata.
- [x] No tenant database was created and no database was reset.
- [ ] Configure a delivery-capable mail transport before expecting real credential email. With the current `log` mailer, the API reports `not_configured` and does not write credentials to logs.
