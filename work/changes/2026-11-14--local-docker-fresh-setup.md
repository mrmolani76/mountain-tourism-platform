# Change Report: Local Docker fresh setup after pull

- **Date**: 2026-11-14
- **By**: Agent
- **Change ID**: local-docker-fresh-setup

## 1. Summary

Pulled `origin/main` to `d8f8d49`, wiped the local mtmtp Docker stack + volumes,
rebuilt both images, and verified the tenant login end to end.

## 2. Before

- Local `main` at `29aca09`; pull blocked by uncommitted `frontend/utils/tenantHost.ts`.
- `mtmtp_spa` container missing; `mtmtp_db` + `mtmtp_laravel` stale (4h up).
- Old Postgres volume from previous runs.
- `tenantHost.ts` had a local `mtmtp.local` (Docker) block; upstream added the
  `mtmp.local` (Nexro-style) block.

## 3. After

- Pull: `29aca09..d8f8d49` fast-forward.
- Local changes stashed, pulled, restored. `tenantHost.ts` conflict resolved by
  keeping both schemes: Nexro `*.mtmp.local` + local Docker `*.mtmtp.local`.
- `docker compose -f docker-compose.dev.yml down -v --remove-orphans`
  (containers, network, `mtmtp_mtmtp_pgdata` removed).
- `docker compose -f docker-compose.dev.yml up -d --build` (fresh images).
- Container start ran: `composer install`, `key:generate`, central `migrate`,
  `tenants:provision-local lab --reset --with-saas-admin --host=lab.local.mtmtp.local`.
- Tenant DB `lab_local` created on Postgres with all module migrations (A–I).

## 4. Reason

User: "pull lo and fresh setup karo" (full fresh, DB wipe chosen).

## 5. Files Affected

- `frontend/utils/tenantHost.ts` (merge conflict resolution only)
- Local only, not committed: `backend-laravel/.env.docker`, Docker volumes
- New: `work/changes/2026-11-14--local-docker-fresh-setup.md`

## 6. Verification / Status

- [x] `GET /api/health/` on `127.0.0.1:8010` → 200, database `ok`
- [x] `GET http://lab.local.mtmtp.local:3000/login` → 200 (SPA served)
- [x] `GET /api/health/` with Host `lab.local.mtmtp.local` → 200
- [x] `GET /api/health/` with Host `saas.mtmtp.local` → 200
- [x] `POST http://lab.local.mtmtp.local:3000/api/auth/login` (`owner@lab.test` / `password123`)
      → 200, `tenant_slug: lab`, session cookie `mountain-tourism-session`
- [ ] Central foundation seed (`db:seed`) not re-run; not required for login
