# mountain-tourism-platform
Mountain Tourism Complex Platform

**Stack (planned):** Django + DRF backend · Nuxt + Vue + Vuetify frontend

**MVP scope:** `document/Mountain_MVP_Scope_v1.pdf`

**AI agents:** read `AGENTS.md` → `CLAUDE.md`. Setup notes: `SETUP_AI_RULES.md`.

## Quick start (Docker, dev)

```bash
docker compose -f docker-compose.dev.yml up -d --build   # or ./start.sh
curl http://127.0.0.1:8010/api/health/                   # {"status":"ok"}
```

SPA: http://127.0.0.1:3000/ · API: http://127.0.0.1:8010/api/health/

Full guide (ports, first run, Windows + Linux, troubleshooting): **[docs/DOCKER_SETUP.md](docs/DOCKER_SETUP.md)**
Inventory stack on the same machine is separate; do not change its files or ports.
