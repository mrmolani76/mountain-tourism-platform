---
title: Nexro-style mtmp.local setup guide + hosts sync scripts
id: 93
priority: high
status: completed
milestone: none
module: docs
area: docs
effort: M
dependencies: 92
bug: none
created: 2026-11-14
updated: 2026-11-14
---

# Nexro-style mtmp.local local setup

## What to do (What)

Add LOCAL_MTMP_SETUP.md (Nexro subdomain pattern: `{slug}.mtmp.local:3000`), hosts:sync artisan + bash script, configure .env.example / provision defaults. Docs + scripts only; do not apply machine sudo unless human asks later.

## Current / Before

- Local hosts documented as `lab.localhost` / `saas.localhost`
- No `hosts:sync` command; no mtmp.local guide

## Expected / After

- `docs/LOCAL_MTMP_SETUP.md`
- `php artisan hosts:sync` + `scripts/mtmp-hosts-sync.sh`
- `.env.example` + provision default `{slug}.mtmp.local`

## Why

User: configure setup like Nexro; give setup guide; Nexro-like script.

## Acceptance Criteria (complete only when all ticked)

- [x] Guide + scripts + artisan hosts:sync
- [x] Env/provision/frontend host parser updated for mtmp.local
- [x] Change report; no sudo applied on machine
- [x] PLAN + SUMMARY

## Files Likely Affected

- `docs/LOCAL_MTMP_SETUP.md`
- `scripts/mtmp-hosts-sync.sh`
- `backend-laravel/app/Console/Commands/SyncHostsFile.php`
- `backend-laravel/.env.example`
- `frontend/utils/tenantHost.ts`

## Completion Log

- **Completed**: 2026-11-14
- **By**: Agent
- **Change report**: `work/changes/2026-11-14--93-nexro-style-mtmp-local-setup.md`
- **Done**:
  - Guide `docs/LOCAL_MTMP_SETUP.md` (Nexro `{slug}.mtmp.local`)
  - `scripts/mtmp-hosts-sync.sh` + `php artisan hosts:sync`
  - `.env.example` / provision / `tenantHost.ts` for `APP_BASE_DOMAIN=mtmp.local`
  - Docs index + MULTI_TENANT_LOCAL + frontend LOCAL_SETUP pointers
- **Not done on machine**: sudo write to `/etc/hosts` (human runs when ready)
- **Verify**: bash + artisan `--dry-run` OK
