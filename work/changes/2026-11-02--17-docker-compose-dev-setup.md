# Change Report: Docker dev setup (backend + Postgres)

- **Date**: 2026-11-02
- **By**: Buffy (coding agent)
- **Change ID**: docker setup (user requested "docker ke sath setup karo"; devops-agent run override granted)

## 1. Summary
Added `backend/Dockerfile` + root `docker-compose.dev.yml` (Postgres 16 + Django 5.2 API) and brought the stack up on this machine; health endpoint returns 200.

## 2. Before
No Docker files in repo. Backend ran only via bare python/venv; repo policy said setup on other machine. User explicitly requested Docker-based setup on this machine (overrides code-only default per devops-agent skill).

## 3. After
- `backend/Dockerfile`: python:3.12-slim, installs `requirements-dev.txt` (includes psycopg2 deps via build-essential/libpq-dev), EXPOSE 8000, dev CMD migrate + runserver
- `docker-compose.dev.yml` (project `mtmtp`):
  - `mtmtp_db`: postgres:16-alpine, mountain_tourism/mt_app/devpass, host port **5433**, volume `mtmtp_pgdata`, pg_isready healthcheck (simplicity: dev-only creds, no secrets)
  - `mtmtp_web`: build from `./backend`, depends_on db healthy, host port **8010** (8000 was occupied on this machine), bind mount `./backend` for dev reload, env for Postgres + TENANCY_CENTRAL_HOSTS
- Stack running: both containers healthy/up, migrations applied (auth, core 0001–0005 incl. item 06 models, saas 0001)

## 4. Reason
User requested environment setup with Docker instead of bare venv.

## 5. Files Affected
- `backend/Dockerfile` (new)
- `docker-compose.dev.yml` (new)
- `work/changes/2026-11-02--17-docker-compose-dev-setup.md`

## 6. Verification / Status
- [x] `docker compose up -d --build` — build OK, both containers start
- [x] Migrations applied inside `mtmtp_web` (Django 5.2.18, config.settings.local)
- [x] `GET http://127.0.0.1:8010/api/health/` → HTTP 200 `{"status":"ok"}`
- [x] Other containers on this machine untouched (ports 8000/8080/3000/3001 belong to existing stacks)
- Notes: dev-only server (runserver); prod path stays gunicorn per TODO-08. No real secrets committed (devpass placeholder, .env not used; compose env inline for dev).

## 7. Usage
```bash
docker compose -f docker-compose.dev.yml -p mtmtp up -d --build   # start
docker logs -f mtmtp_web                                          # follow logs
docker compose -f docker-compose.dev.yml -p mtmtp down            # stop (keeps volume)
```
