# Docker Setup — Mountain Tourism Platform (dev)

Dev stack via Docker Compose. No manual Python / Postgres / Node setup needed.
Prod path is gunicorn on a VPS (item 08); this guide covers **dev only**.

The InventorySystem-docker stack is a separate project on the same machine.
Do not edit its files, ports, containers, or volumes.

---

## Services and ports

| Service | Container | Host port → container | What it is |
|---------|-----------|-----------------------|------------|
| `db` | `mtmtp_db` | **5433** → 5432 (loopback) | Postgres 16 (central DB) |
| `web` | `mtmtp_web` | **8010** → 8000 (loopback) | Django 5 API (runserver, auto-migrate) |
| `spa` | `mtmtp_spa` | **3000** → 80 (loopback) | Nuxt SPA (static build via nginx, `/api` proxied to `web:8000`) |

Compose project name: **`mtmtp`** (explicit `name:` in `docker-compose.dev.yml`).
Network: `mtmtp_net` (bridge). Volume: `mtmtp_mtmtp_pgdata` (keep it; never `down -v`).

Inventory stack ports to avoid: 8080, 3001, 3307, 3000 (landing, if its profile is on).

---

## Requirements

- Docker Engine 24+ or Docker Desktop (Windows: WSL 2 backend enabled, ≥ 6 GB RAM)
- Docker Compose v2 (`docker compose version`)
- Git

Linux quick check: `docker ps` works without sudo (user in `docker` group).

---

## First run

```bash
git clone <repo-url> && cd mountain-tourism-platform

# Build + start (migrations run automatically on web start)
docker compose -f docker-compose.dev.yml up -d --build

# Optional: seed demo buildings/outlets (§2 of MVP scope), idempotent
docker compose -f docker-compose.dev.yml exec web python manage.py seed_foundation

# Optional: create an admin user for the central app
docker compose -f docker-compose.dev.yml exec web python manage.py createsuperuser
```

Verify:

```bash
curl http://127.0.0.1:8010/api/health/    # {"status":"ok"}
curl -I http://127.0.0.1:3000/            # HTTP 200
```

---

## Daily use

```bash
./start.sh     # build (if needed) + up -d
./stop.sh      # stop containers, KEEP volumes (no down -v)
./status.sh    # container state + health endpoints

docker compose -f docker-compose.dev.yml logs -f web
docker compose -f docker-compose.dev.yml exec web python manage.py migrate
docker compose -f docker-compose.dev.yml exec web python manage.py test
```

---

## URLs

| What | URL |
|------|-----|
| SPA | http://127.0.0.1:3000/ |
| API health | http://127.0.0.1:8010/api/health/ |
| Django admin (central) | http://127.0.0.1:8010/admin/ |

The SPA is a static build. It calls **same-origin** `/api`; the SPA nginx proxies
`/api/` → `http://web:8000`. Session cookie (`SameSite=Lax`) works because browser
stays on one origin. Do not point the SPA at `http://web:8000` from the browser;
that hostname exists only inside the compose network.

### Tenant hosts (optional)

Tenant routing is by Host header. For a local tenant on this machine:

1. Add to hosts file (`/etc/hosts`, or `C:\Windows\System32\drivers\etc\hosts` on Windows):
   `127.0.0.1 lab.local.mountain.local`
2. Open `http://lab.local.mountain.local:3000/`
3. `.local.mountain.local` is allowed in `ALLOWED_HOSTS` and is not a central host,
   so Django takes the tenant path.

Physical tenant DBs are not created automatically yet (provisioning is code-only;
see `docs/HOSTING.md`). Until then, use the central app at `127.0.0.1`.

---

## Windows notes

- Install Docker Desktop, enable WSL 2, restart.
- Use PowerShell in the repo root; `docker compose` commands are identical.
- Bind-mount performance: repo under the WSL filesystem (`\\wsl$\...`) is faster than under `C:\`.
- If port 3000/8010/5433 is taken, change the host side mapping in `docker-compose.dev.yml`.

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| `port is already allocated` | Find owner: `ss -tlnp \| grep -E '3000\|8010\|5433'`; change host port in compose |
| `web` unhealthy | `docker compose -f docker-compose.dev.yml logs web`; usually DB not ready or migrate failure |
| SPA 502 on `/api/*` | `web` not healthy yet; wait or check `logs web` |
| Stale code in container | Backend is bind-mounted; restart `web`. Frontend is a static build: rebuild with `up -d --build spa` |
| Permission denied on `/etc/hosts` | Edit as admin (Windows) or `sudo` (Linux) |
| Want a clean DB | `docker compose -f docker-compose.dev.yml down` then remove volume **only if you accept data loss**: `docker volume rm mtmtp_mtmtp_pgdata` |

Never run `docker compose down -v` unless you intend to wipe the dev database.

---

## Environment

Dev-only credentials are inline in `docker-compose.dev.yml` (`devpass`, throwaway
secret). They are not used anywhere else. Real secrets never go in the repo;
`backend/.env.example` documents the variables for other setups.

Build-time SPA config (baked into the static build, `ssr: false`):

| Arg | Default | Meaning |
|-----|---------|---------|
| `NUXT_PUBLIC_API_BASE_URL` | `""` (empty) | Empty = same-origin `/api` (recommended). Set a full URL only for split-origin deploys |
| `NUXT_PUBLIC_ENABLE_REALTIME` | `false` | Realtime off |
| `BACKEND_URL` | `http://web:8000` | Used by the Nuxt dev proxy only; irrelevant for the static build |
| `ENV` | `local` | Build label |
