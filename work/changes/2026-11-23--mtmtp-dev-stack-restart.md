# Change Report: mtmtp dev stack restart + correct client URL

- **Date**: 2026-11-23
- **By**: Agent
- **Change ID**: runtime-only

## 1. Summary

Restarted the stopped mtmtp Docker dev stack and confirmed the correct client URL is `http://lab.local.mtmtp.local:3000/login`, not `demo.mtmp.local`.

## 2. Before

- `docker ps -a`: `mtmtp_laravel`, `mtmtp_spa`, `mtmtp_db` all `Exited` since 2026-11-16 18:14 UTC (fast shutdown, never restarted).
- Ports 3000 / 8010 / 5433 had no listener; no `node` / `php` process on the host.
- Browser at `http://demo.mtmp.local:3000/login` → `ERR_NAME_NOT_RESOLVED` (no hosts entry for that name).
- `C:\Windows\System32\drivers\etc\hosts` had only `127.0.0.1 lab.local.mtmtp.local saas.mtmtp.local`.
- `backend-laravel/.env` on the host is empty (0 bytes), but the stack uses `backend-laravel/.env.docker` via bind mount.

## 3. After

- `docker compose -f docker-compose.dev.yml up -d` → `mtmtp_db` (healthy), `mtmtp_laravel`, `mtmtp_spa` all `Up`.
- Laravel container was recreated, which applied the pending `mtmtp_vendor:/app/vendor` named-volume change from `docker-compose.dev.yml`.
- Bootstrap ran composer install, `key:generate`, `migrate`, `tenants:provision-local lab`, then `php artisan serve`.
- `GET http://127.0.0.1:8010/api/health/` → 200.
- `GET http://lab.local.mtmtp.local:3000/login` → 200.
- `GET http://lab.local.mtmtp.local:3000/api/health/` → 200.
- `GET http://demo.mtmp.local:3000/login` → still fails (`remote name could not be resolved`); that hostname is not in hosts.

## 4. Reason

User: "last time to chal rha tha ab kya issue aagya check kro and fix karo". Root cause: the Docker stack had been stopped for ~6 days (Docker restart) and the hostname used in the browser was wrong for this machine.

## 5. Files Affected

- Runtime only (Docker containers). No application source changed.
- `work/changes/2026-11-23--mtmtp-dev-stack-restart.md` (this report).
- `work/SUMMARY.md` §4 Changes (new entry).

## 6. Verification / Status

- [x] Containers up and healthy.
- [x] API health 200 (direct + via SPA :3000).
- [x] Login page 200 on `lab.local.mtmtp.local:3000`.
- [ ] No migrations pending (bootstrap `migrate --force` completed in container).
- [ ] Note: docs (`docs/LOCAL_MTMP_SETUP.md`, `backend-laravel/.env.example`) still use `demo.mtmp.local` / `mtmp.local` in examples; this machine's hosts file uses `lab.local.mtmtp.local`. Not changed here.
