# Local SPA setup

How local multi-tenant hosting works for the Nuxt SPA against Laravel.

**This agent host does not run Docker / composer / migrate.** Use a setup machine for smoke.

---

## Hosts

**Preferred (Nexro-style):** see [`docs/LOCAL_MTMP_SETUP.md`](../../docs/LOCAL_MTMP_SETUP.md).

| Host | Role | DB | Purpose |
|------|------|----|---------|
| `saas.mtmp.local` | **Central** (landlord) | central SaaS | Super admin: tenants, domains, activate/deactivate |
| `demo.mtmp.local` / `{slug}.mtmp.local` | Tenant | that slug’s DB | Day-to-day SPA + Core staff auth |
| `saas.localhost` / `lab.localhost` | Legacy local | same idea | Still parsed in `utils/tenantHost.ts` |

Optional dotted form: `lab.local.mountain.local`, `saas.local.mountain.local`.
Parser: `mtmp.local`, `.localhost`, and `.mountain.local` in `utils/tenantHost.ts`.

- **Central host is not a tenant.** Tenant Core routes must not be called from `saas.*`.
- **Tenant host** resolves Domain → Tenant → tenant connection.

---

## One-time machine setup (setup machine only)

### 1. /etc/hosts

```bash
127.0.0.1 saas.localhost lab.localhost
```

### 2. Backend

```bash
cd backend-laravel
cp .env.example .env
# set APP_KEY, DB_*, FRONTEND_URL=http://lab.localhost:3000
composer install
php artisan migrate
php artisan tenants:migrate   # if tenancy commands exist
php artisan serve --host=0.0.0.0 --port=8000
```

### 3. Frontend

```bash
cd frontend
cp .env.example .env
# BACKEND_URL=http://lab.localhost:8000
npm install
npm run dev -- --host 0.0.0.0 --port 3000
```

Open `http://lab.localhost:3000`.

Vite proxies `/api` and `/sanctum` to `BACKEND_URL`.

---

## Sanity checks

| Check | Expect |
|-------|--------|
| `GET /sanctum/csrf-cookie` via Nuxt origin | 204 / Set-Cookie |
| `POST /api/auth/login` | session + staff |
| `GET /api/auth/me` | current staff |
| `GET /api/core/menus` | vertical nav JSON |

See also: `SPA_API_AUTH.md`.
