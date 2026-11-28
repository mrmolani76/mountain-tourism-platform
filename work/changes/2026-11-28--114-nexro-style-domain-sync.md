# Change Report: Nexro-Style SaaS Tenant Domain Sync

- **Date**: 2026-11-28
- **By**: Copilot
- **Change ID**: 114 / BUG-TENANT-002

## 1. Summary
Added automatic local tenant-host synchronization and dynamic Sanctum stateful hosts to the SaaS tenant onboarding flow.

## 2. Before
The platform had `php artisan hosts:sync`, but SaaS tenant creation did not run it. Sanctum's stateful host configuration in the dev Compose environment listed only the preconfigured `lab` and `saas` hosts.

## 3. After
After successful local tenant database, migration, and Owner provisioning, the SaaS service runs `hosts:sync`. Its result (`synced`, `permission_required`, `failed`, or `not_applicable`) is returned to the create UI with an actionable status. Registered `saas_domain.hostname` values are merged into Sanctum stateful config with the configured SPA port. Dev Compose can mount the Windows hosts file at `/etc/hosts` through the repository-root `HOSTS_FILE_SOURCE` variable.

Production does not run local hosts-file synchronization. Owner email remains dependent on a delivery-capable mailer.

## 4. Reason
The previous browser acceptance showed that a correctly provisioned tenant domain could not resolve on Windows. Nexro synchronizes tenant hosts after domain creation and allows registered tenant domains to use Sanctum cookie auth; this change brings those SaaS-specific behaviors into the platform.

## 5. Files Affected
- `backend-laravel/Modules/Saas/App/Services/TenantDomainSyncService.php`
- `backend-laravel/Modules/Saas/App/Services/TenantService.php`
- `backend-laravel/Modules/Saas/App/Http/Controllers/TenantController.php`
- `backend-laravel/app/Providers/AppServiceProvider.php`
- `backend-laravel/config/sanctum.php`
- `backend-laravel/.env.example`
- `frontend/composables/api-clients/useSaasApiClient.ts`
- `frontend/pages/saas/tenants/create.vue`
- `docker-compose.dev.yml`
- `docs/LOCAL_MTMP_SETUP.md`
- `work/inprogress/114-nexro-style-saas-super-admin-ui.md`
- `work/SUMMARY.md`

## 6. Verification / Status
- [x] Source changes reviewed for central-domain boundaries and production host-sync guard.
- [x] `git diff --check` passes.
- [ ] Browser and automated checks not run under the active code-only workflow.
- [ ] Windows hosts-file mount and tenant-owner login require runtime setup/verification; no Docker restart or OS hosts-file edit was performed.
