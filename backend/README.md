# Backend API — Mountain Tourism Platform

Django + Django REST Framework backend. Phase 1 = Module A (core + super admin).
Frontend (Nuxt/Vuetify) is deferred.

## Run locally

```bash
cd backend
cp .env.example .env
# edit .env if needed

python3 -m venv .venv   # optional but recommended
source .venv/bin/activate

pip install Django djangorestframework   # or use system packages

python3 manage.py migrate
python3 manage.py runserver 0.0.0.0:8000
```

## Endpoints

| Method | Path | Auth | Purpose |
|--------|------|------|---------|
| GET | /api/health/ | none | Health check |
| GET | /api/schema/ | none | OpenAPI schema |
| GET | /api/ | session | API root stub |
| GET/POST | /admin/ | staff | Django admin |

## Settings

- `config/settings/base.py` — shared
- `config/settings/local.py` — dev (DEBUG=True, sqlite default)
- `config/settings/prod.py` — production (Postgres required, security headers)

DJANGO_SETTINGS_MODULE defaults to `config.settings.local`.

## Tests

```bash
python3 manage.py test
```
