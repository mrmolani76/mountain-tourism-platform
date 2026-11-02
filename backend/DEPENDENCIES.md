# Backend dependencies (declared — not installed here)

**Policy:** Document packages only. Do **not** run `pip install`, venv, migrate, or createdb from this file unless the user explicitly opens setup.

Stack: Django + Django REST Framework. No SSO packages. No new third-party tenancy package (custom `tenancy/`).

## Runtime (required)

| Package | Role | Notes |
|---------|------|--------|
| `Django` | Web framework | Project uses 5.x style settings/apps (scaffold era) |
| `djangorestframework` | API / serializers (DTO boundary) / ViewSets | |
| `psycopg` or `psycopg2-binary` | Postgres driver | Needed when DB_ENGINE is Postgres; sqlite works without it for smoke |

Python: **3.12** recommended (see `docs/CI.md`).

## Dev / CI (optional later)

| Package | Role |
|---------|------|
| `ruff` | Lint |
| `pytest` + `pytest-django` | Tests (if CI switches from `manage.py test`) |

## Explicitly out of scope

- SSO / SAML / OAuth / MFA libraries
- `django-tenants` / stancl equivalent — custom `tenancy/` instead
- Frontend npm packages (Nuxt deferred)

## Install later (setup — do not run now)

When setup is allowed:

```bash
cd backend
python3 -m venv .venv
source .venv/bin/activate
pip install Django djangorestframework
# Postgres:
# pip install "psycopg[binary]"
```

A frozen `requirements.txt` / `requirements-dev.txt` can be generated from this list at setup time. Until then this MD is the source of truth for intended packages.

## Related

- [`README.md`](README.md) — run notes (setup when allowed)
- [`docs/CI.md`](../docs/CI.md) — expects requirements files once setup lands
- [`docs/ARCHITECTURE.md`](../docs/ARCHITECTURE.md) — DB-per-tenant + modules
