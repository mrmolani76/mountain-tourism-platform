# Change Report: Nexro Tenant-Domain Resolution Retest

- **Date**: 2026-11-28
- **By**: Copilot
- **Change ID**: 114 / BUG-TENANT-002

## 1. Summary
Compared the reported tenant-host DNS failure with Nexro's Windows hosts synchronization setup, clarified the remaining issue title, and returned the bug to in-progress.

## 2. Before
The previous retest recorded tenant-domain access as pending after a host-file write was denied. The app code and dev Compose had since gained Nexro-style `hosts:sync` and an optional Windows hosts-file bind mount.

## 3. After
The user's screenshot confirms `ERR_NAME_NOT_RESOLVED` for `copilot-qa-20261128-0655.mtmp.local:3000`. Windows hosts does not contain that hostname, and the repository-root `.env` is absent. Nexro sets `HOSTS_FILE_SOURCE` in that root `.env` to the Windows hosts file, mounts it at `/etc/hosts`, then syncs registered tenant domains. The equivalent source changes exist here, but the local mount has not been configured/applied by recreating the Laravel service.

## 4. Reason
The failure occurs during DNS resolution, before the tenant SPA or login API can receive a request. This is not a credential or CSRF failure.

## 5. Files Affected
- `work/bugs/BUG-TENANT-002-saas-onboarding-does-not-provision-database.md`
- `work/bugs/INDEX.md`
- `work/SUMMARY.md`
- `work/inprogress/114-nexro-style-saas-super-admin-ui.md`
- `work/bugs/BUG-TENANT-002-saas-onboarding-does-not-provision-database.md`

## 6. Verification / Status
- [x] Browser screenshot evidence reviewed: `ERR_NAME_NOT_RESOLVED`.
- [x] Windows hosts queried; tenant alias absent.
- [x] Repository-root `.env` presence checked; absent.
- [x] Nexro compose and change record reviewed for its `HOSTS_FILE_SOURCE` bind-mount pattern.
- [ ] Tenant-domain login remains blocked pending local Windows hosts-file mount configuration and Laravel service recreation.
- [ ] No Docker command, Windows hosts edit, database change, test, or build was run.
