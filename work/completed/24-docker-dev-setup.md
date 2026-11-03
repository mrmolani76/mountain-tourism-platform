---
title: Docker dev setup — inventory-style dev stack (compose, docs, scripts, verify)
id: 24
priority: high
status: completed
milestone: 1
module: infra
area: devops
effort: M
dependencies: 17
bug: none
created: 2026-11-03
updated: 2026-11-03
---

# Docker dev setup — inventory-style dev stack

## What to do (What)
Bring the Mountain Tourism Platform Docker dev setup to the same standard as the
InventorySystem-docker dev stack, without touching the inventory stack:

- `docker-compose.dev.yml`: explicit project `name`, loopback-bound ports, healthchecks, working SPA service
- Fix SPA → Django wiring inside Docker (same-origin `/api` via nginx proxy → `web:8000`)
- `.dockerignore` for backend and frontend
- `docs/DOCKER_SETUP.md` (ports, first-run, Windows + Linux, troubleshooting)
- Root `start.sh` / `stop.sh` / `status.sh` (Docker-based, keep volumes, no `down -v`)
- README quick-start section for Docker
- Build + `up` + verify on this machine (user approved running Docker for this item)

## Current / Before
- `docker-compose.dev.yml` exists (tracked), no explicit `name:` (project resolves from `-p` flag or folder); mtmtp_web/mtmtp_db running (up 15h, project `mtmtp`)
- SPA service defined but never built: `docker-up.log` (tracked) shows `nuxt generate` failed with `ParseError: /app/nuxt.config.ts:154:0` (stale; file is now 136 lines, ASCII)
- `frontend/docker/nginx-spa-proxy.conf` proxies `/api/` to `http://backend:8000` — service `backend` does not exist in compose (actual: `web`); SPA would 502
- Compose sets `NUXT_PUBLIC_API_BASE_URL: http://backend:8000` — browser cannot resolve `backend`; wrong for a static SPA
- No `docs/DOCKER_SETUP.md`, no root start/stop/status scripts, no `.dockerignore` files
- Inventory stack (`/var/www/html/InventorySystem-docker`, containers `inventory_*`) must stay untouched

## Expected / After
- `docker compose -f docker-compose.dev.yml up -d --build` works on a fresh clone (Linux + Windows notes in docs)
- Port map: web 8010 → 8000, db 127.0.0.1:5433 → 5432, spa 127.0.0.1:3000 → 80/3000 (documented)
- SPA reachable at `http://127.0.0.1:3000/`, API health 200 at `http://127.0.0.1:8010/api/health/`
- SPA calls same-origin `/api`, nginx proxies to `web:8000`; session cookie flow works (SameSite=Lax)
- `./start.sh`, `./stop.sh`, `./status.sh` wrap compose; `stop.sh` never wipes volumes
- Inventory containers/volumes/files unchanged

## Why
User asked: "docker setup karo jese inventory system docker setup hua hai vese isko bhi docker setup krna hai lekin dusre wale docker delete nhi hone chahiye" (2026-11-03). Verified inventory stack is running and must stay untouched.

## Acceptance Criteria (complete only when all ticked)
- [x] Compose has explicit `name: mtmtp`, loopback-bound ports, db + web healthchecks
- [x] SPA image builds; `mtmtp_spa` serves SPA with `/api` proxied to `web:8000`
- [x] `http://127.0.0.1:8010/api/health/` → 200; `http://127.0.0.1:3000/` → 200
- [x] `docs/DOCKER_SETUP.md` written (ports, first-run, Windows + Linux, troubleshooting)
- [x] `start.sh` / `stop.sh` / `status.sh` added, executable, no `down -v`
- [x] `.dockerignore` added for `backend/` and `frontend/`
- [x] Inventory stack untouched (no file changes under `/var/www/html/InventorySystem-docker`, no container/volume changes)
- [x] `work/changes/2026-11-03--24-docker-dev-setup.md` written
- [x] PLAN.md + SUMMARY.md updated

## Files Likely Affected
- `docker-compose.dev.yml`
- `frontend/docker/nginx-spa-proxy.conf`
- `backend/.dockerignore`, `frontend/.dockerignore`
- `docs/DOCKER_SETUP.md` (new)
- `start.sh`, `stop.sh`, `status.sh` (new)
- `README.md`
- `work/PLAN.md`, `work/SUMMARY.md`, `work/MEMORY.md`

## Notes / Follow-ups
- Prod path stays gunicorn on VPS (item 08); no prod compose in this item (user chose dev-only)
- `docker-up.log` and `frontend/nuxt-config-utf8.ts` are tracked stray files — flagged to user, not deleted
- Inventory host ports to avoid: 8080 (api), 3001 (spa), 3000 (landing, if profile enabled), 3307 (db)
- Secrets: dev-only inline creds stay; no real secrets committed

---

## Return to Pending Log (when work stops midway)

None.

---

## Completion Log (when work is complete)

- **Completed by**: OpenCode agent (devops-agent)
- **Date**: 2026-11-03
- **What was done**: compose project name + network/port/healthcheck fixes; SPA static build fixed (build args + `/app/.output` copy); nginx SPA config rewritten (valid server block, `/api` → `web:8000`); `.dockerignore` backend/frontend; `docs/DOCKER_SETUP.md`; root `start.sh` / `stop.sh` / `status.sh`; README quick-start; stack built and verified healthy on this machine; inventory untouched.
- **Files changed**: `docker-compose.dev.yml`, `frontend/Dockerfile`, `frontend/docker/nginx-spa-proxy.conf`, `backend/.dockerignore`, `frontend/.dockerignore`, `docs/DOCKER_SETUP.md`, `start.sh`, `stop.sh`, `status.sh`, `README.md`
- **Verification**: `docker compose -f docker-compose.dev.yml up -d --build` OK; `mtmtp_db`/`mtmtp_web`/`mtmtp_spa` healthy; `GET 127.0.0.1:8010/api/health/` → 200; `GET 127.0.0.1:3000/` → 200; `GET 127.0.0.1:3000/api/health/` → 200; inventory containers/files unchanged
- **Change report**: `work/changes/2026-11-03--24-docker-dev-setup.md`
- **PLAN / SUMMARY**: updated yes
