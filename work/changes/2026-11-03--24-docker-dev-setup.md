# Change Report: Docker dev setup — inventory-style (item 24)

- **Date**: 2026-11-03
- **By**: OpenCode agent (devops-agent; user approved running Docker on this machine)
- **Change ID**: 24

## 1. Summary
Brought the Mountain Tourism Platform dev stack to the inventory-docker standard:
explicit compose project name, working Nuxt SPA (static build + nginx + same-origin
`/api` proxy), healthchecks, docs, and root helper scripts. Inventory stack untouched.

## 2. Before
- `docker-compose.dev.yml`: no `name:` (project from `-p` flag / folder); `db` not on the app network; `spa` on `mtmtp_net` alone (could not resolve `web`); spa port mapped to container `3000` but nginx listens on `80`; spa never built (`docker-up.log` showed `nuxt generate` failure, since fixed upstream); `NUXT_PUBLIC_API_BASE_URL` pointed at `http://backend:8000` (browser-unresolvable; service name did not exist)
- `frontend/docker/nginx-spa-proxy.conf`: `location` blocks with no `server {}` wrapper (nginx emerg); proxied to non-existent `backend:8000`
- `frontend/Dockerfile`: copied `/app/.output/public`, but `nitro.output.publicDir = '.output/'` emits the static site directly into `/app/.output` (build failed at final stage)
- No `docs/DOCKER_SETUP.md`, no root start/stop/status scripts, no `.dockerignore`

## 3. After
- `docker-compose.dev.yml`: `name: mtmtp`; all services on `mtmtp_net`; db (5433), web (8010), spa (127.0.0.1:3000 → 80); db + web + spa healthchecks; `DJANGO_CSRF_TRUSTED_ORIGINS` set for `localhost:3000` / `127.0.0.1:3000`; SPA build args (empty api base = same-origin `/api`)
- `frontend/Dockerfile`: declares `NUXT_PUBLIC_API_BASE_URL` / `NUXT_PUBLIC_ENABLE_REALTIME` / `ENV` / `BACKEND_URL` build args; copies `/app/.output` (matches `nitro.output.publicDir`)
- `frontend/docker/nginx-spa-proxy.conf`: valid `server {}` block, listens `:80`, `/_nuxt/` immutable cache, SPA shell `no-cache` + `/200.html` fallback, `/api/` → `http://web:8000` with `X-Forwarded-*`; security headers on every location (nginx add_header inheritance)
- `backend/.dockerignore`, `frontend/.dockerignore`: keep images lean, no local DBs/env/node_modules in context
- `docs/DOCKER_SETUP.md` (new): services/ports, first run, daily use, URLs, tenant hosts, Windows notes, troubleshooting
- `start.sh` / `stop.sh` / `status.sh` (new, executable): compose wrappers; stop never wipes volumes
- `README.md`: Docker quick-start section
- Verified on this machine: `mtmtp_db` / `mtmtp_web` / `mtmtp_spa` all healthy; `http://127.0.0.1:8010/api/health/` → 200; `http://127.0.0.1:3000/` → 200 (SPA HTML); `http://127.0.0.1:3000/api/health/` → 200 (same-origin proxy → Django)

## 4. Reason
User request (2026-11-03): "docker setup karo jese inventory system docker setup hua hai vese isko bhi docker setup krna hai lekin dusre wale docker delete nhi hone chahiye". Scope confirmed via question: dev-only, and running Docker on this machine approved.

## 5. Files Affected
- `docker-compose.dev.yml`
- `frontend/Dockerfile`
- `frontend/docker/nginx-spa-proxy.conf`
- `backend/.dockerignore` (new)
- `frontend/.dockerignore` (new)
- `docs/DOCKER_SETUP.md` (new)
- `start.sh`, `stop.sh`, `status.sh` (new)
- `README.md`
- `work/inprogress/24-docker-dev-setup.md`
- `work/PLAN.md`, `work/SUMMARY.md`

## 6. Verification / Status
- [x] `docker compose -f docker-compose.dev.yml up -d --build` — images build, containers start
- [x] `mtmtp_web` healthy (auto-migrate on start), `mtmtp_db` healthy, `mtmtp_spa` healthy
- [x] `GET /api/health/` → 200 (direct 8010 and via SPA proxy :3000)
- [x] `GET /` on SPA → 200, Vue/Nuxt assets load
- [x] Inventory stack untouched: no file writes under `/var/www/html/InventorySystem-docker`; `inventory_*` containers/volumes unchanged (`inventory_spa` was already `unhealthy` before this work; not in scope, not touched)
- [x] No `down -v`; `mtmtp_mtmtp_pgdata` preserved (db recreated for network change only)
- Notes: deep routes (`/login`, `/modules/*`) return nginx 301 → trailing slash (normal for prerendered static Nuxt); SPA handles client-side routing. `nitro.output.publicDir='.output/'` override in `nuxt.config.ts` kept as-is (app code; flagged to user).

## 7. Usage
```bash
./start.sh          # build + up -d (keeps volumes)
./status.sh         # containers + health
./stop.sh           # stop (keeps volumes)
docker compose -f docker-compose.dev.yml logs -f web
```
Full guide: `docs/DOCKER_SETUP.md`
