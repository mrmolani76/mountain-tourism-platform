# Backend structure gaps (ideal tree vs repo)

- **Date**: 2026-11-02
- **Ideal source**: User-proposed `mountain-tourism-platform/` tree (config / tenancy / modules / common / tests / uv)
- **Policy**: Document only on this machine. Physical setup / `uv sync` / migrate = **other machine**.
- **Related**: [`ARCHITECTURE.md`](ARCHITECTURE.md), [`../backend/modules/README.md`](../backend/modules/README.md), [`../backend/DEPENDENCIES.md`](../backend/DEPENDENCIES.md)

---

## 1. Summary

| Bucket | Status |
|--------|--------|
| `tenancy/`, `modules/` skeleton, `config/`, `docs/`, `work/`, `.github/` | Largely present |
| `common/`, `pyproject.toml`, `uv.lock`, split `tests/` | **Missing** |
| Per-module `admin.py`, root `.env.example` | **Missing** |
| Settings / views naming | Present under different names |

---

## 2. Present (equivalent names OK)

| Ideal | Current | Notes |
|-------|---------|-------|
| `config/settings/base.py` | same | |
| `config/settings/development.py` | `local.py` | Rename optional |
| `config/settings/production.py` | `prod.py` | Rename optional |
| `config/urls.py`, `asgi.py`, `wsgi.py`, `db_router.py` | same | |
| `tenancy/middleware.py`, `context.py`, `connections.py`, `provision.py` | same | Also `management/commands/` |
| `modules/{saas,core,hotel,folio,pos,rental,services,transport,weather,reports}/` | same | Domain stubs empty except saas/core partial |
| `modules/*/models|serializers|services|permissions|urls|apps|migrations` | same | |
| Ideal `controllers/` | **`views/`** | DRF convention; same role |
| `manage.py` | same | |
| `docs/`, `scripts/`, `work/`, `.github/` | same | |
| `.gitignore`, root `README.md` | same | |
| `.env.example` | **`backend/.env.example`** | Not at repo root |

---

## 3. Missing — create later (checklist)

### 3.1 Package / lock

| Path | Why | Other-machine note |
|------|-----|--------------------|
| `backend/pyproject.toml` | Declared deps + tool config (uv/poetry-style) | Generate from [`DEPENDENCIES.md`](../backend/DEPENDENCIES.md); run `uv lock` there |
| `backend/uv.lock` | Locked installs | Create only when setup allowed on target machine |
| `backend/requirements.txt` (optional) | CI currently mentions it in `docs/CI.md` | Can mirror pyproject export |

### 3.2 Shared `common/` package (entire tree missing)

```text
backend/common/
  exceptions/
  pagination/
  responses/
  utilities/
  constants/
```

| Folder | Intended use |
|--------|----------------|
| `exceptions/` | Domain/API exception types |
| `pagination/` | Shared DRF pagination classes |
| `responses/` | Shared JSON envelope helpers (if adopted) |
| `utilities/` | Small shared helpers (no domain rules) |
| `constants/` | Cross-module constants |

**Rule:** Business workflows stay in `modules/*/services/`, not in `common/`.

### 3.3 Tests layout

| Ideal | Current | Gap |
|-------|---------|-----|
| `backend/tests/unit/` | — | Missing folder + move/split |
| `backend/tests/integration/` | — | Missing |
| `backend/tests/api/` | flat `test_*.py` at `tests/` | Reorganize when tests are run on other machine |

Current files (to relocate later):

- `backend/tests/test_auth.py`
- `backend/tests/test_saas_registry.py`
- `backend/tests/test_tenant_db_isolation.py`

### 3.4 Per-module `admin.py`

Missing under each of:

`saas`, `core`, `hotel`, `folio`, `pos`, `rental`, `services`, `transport`, `weather`, `reports`

(Django admin optional for MVP; still listed as tree gap.)

### 3.5 Root env example

| Ideal | Gap |
|-------|-----|
| `/.env.example` | Missing; only `backend/.env.example` exists. Either add root pointer file or document that backend path is canonical. |

### 3.6 Optional renames (not blockers)

| From | To (ideal) |
|------|------------|
| `config/settings/local.py` | `development.py` |
| `config/settings/prod.py` | `production.py` |
| `modules/*/views/` | `controllers/` (only if team wants Laravel-like naming; DRF prefers `views`) |

---

## 4. Present folders but product-empty (not tree gaps)

Skeleton exists; **implementation** pending (phases / todos):

| Module | Tree | Product fill |
|--------|------|--------------|
| `saas` | OK | Partial (registry, SaasUser, APIs) |
| `core` | OK | Partial (Staff, Outlet stub, auth) |
| `hotel` … `reports` | OK | Empty stubs — Phases 2–5 |

Kanban still open: **06, 07, 08, 09, 10**.

---

## 5. Ideal tree (reference)

```text
mountain-tourism-platform/
├── backend/
│   ├── config/
│   │   ├── settings/   # base, development, production
│   │   ├── urls.py, asgi.py, wsgi.py, db_router.py
│   ├── tenancy/
│   ├── modules/        # saas, core, hotel, folio, pos, rental, services, transport, weather, reports
│   │   └── <mod>/      # models, controllers|views, serializers, services, permissions, urls, admin, apps, migrations
│   ├── common/         # MISSING
│   ├── tests/          # unit|integration|api MISSING (flat tests exist)
│   ├── manage.py
│   ├── pyproject.toml  # MISSING
│   └── uv.lock         # MISSING
├── docs/
├── scripts/
├── work/
├── .github/
├── .env.example        # MISSING at root (backend/.env.example exists)
├── .gitignore
└── README.md
```

---

## 6. Suggested fill order (other machine / later Agent tasks)

1. `pyproject.toml` (+ lock when setup allowed) aligned with `DEPENDENCIES.md`
2. Scaffold empty `backend/common/**` packages
3. Split `tests/` into `unit/`, `integration/`, `api/`
4. Add empty `admin.py` per module (or skip until needed)
5. Root `.env.example` stub pointing at `backend/.env.example`
6. Optional renames (`local`→`development`, etc.)

Do **not** run `uv sync` / migrate on the machine that is code-only.

---

## 7. Explicit non-goals on this machine

- No `pip` / `uv` install
- No Postgres createdb / migrate apply
- No hosts / VPS setup
