# Change Report: Nexro-style mtmp.local setup guide + hosts:sync

- **Date**: 2026-11-14
- **By**: Agent
- **Change ID**: 93

## 1. Summary

Added LOCAL_MTMP_SETUP guide, `hosts:sync` artisan + bash script, and wired defaults to `{slug}.mtmp.local` (Nexro-style). Did not apply sudo/hosts on this machine.

## 2. Before

- Local docs / examples used `lab.localhost` / `saas.localhost`
- No `hosts:sync` command; no `scripts/mtmp-hosts-sync.sh`
- `tenants:provision-local` default host = `{slug}.localhost`
- SPA `tenantHost.ts` did not parse `.mtmp.local`

## 3. After

- Preferred local URL: `http://demo.mtmp.local:3000` (central: `saas.mtmp.local`)
- Guide: `docs/LOCAL_MTMP_SETUP.md`
- `php artisan hosts:sync` (+ `--dry-run` / `--remove-all`) and `scripts/mtmp-hosts-sync.sh`
- `.env.example`: `APP_BASE_DOMAIN=mtmp.local`, central `saas.mtmp.local`
- Provision default host: `{slug}.{APP_BASE_DOMAIN}`
- Frontend parser understands `.mtmp.local`

## 4. Reason

User asked for Nexro-like subdomain setup guide and Nexro-style hosts sync script, configured in repo (no machine sudo unless human runs it).

## 5. Files Affected

- `docs/LOCAL_MTMP_SETUP.md` (new)
- `docs/README.md`
- `docs/MULTI_TENANT_LOCAL.md`
- `scripts/mtmp-hosts-sync.sh` (new)
- `backend-laravel/config/hosts.php` (new)
- `backend-laravel/app/Console/Commands/SyncHostsFile.php` (new)
- `backend-laravel/app/Console/Commands/ProvisionLocalTenantCommand.php`
- `backend-laravel/.env.example`
- `frontend/utils/tenantHost.ts`
- `frontend/.env.example`
- `frontend/docs/LOCAL_SETUP.md`
- `work/inprogress/93-…` → `work/completed/93-…`
- `work/PLAN.md`, `work/SUMMARY.md`

## 6. Verification / Status

- [x] `bash scripts/mtmp-hosts-sync.sh --dry-run` shows managed block for mtmp.local / saas / demo / lab
- [x] `php artisan hosts:sync --dry-run` lists (DB optional; falls back to central/base when pgsql driver missing)
- [x] `php artisan list hosts` shows `hosts:sync`
- [ ] Machine `/etc/hosts` not written (by design; human runs sudo later)
- [ ] Notes: real `.env` on this machine still legacy `saas.localhost` until human copies from `.env.example`
