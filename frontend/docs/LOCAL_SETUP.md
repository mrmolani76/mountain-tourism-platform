# Local SPA setup — Mountain (eQMS LOCAL_SETUP style)

How local multi-tenant hosting should work for the Nuxt SPA against Laravel Module 1.
Pattern source: eQMS `complere/LOCAL_SETUP.md` + `qualitycenter-spa` CLAUDE.md.

**This agent host does not run Docker / composer / migrate.** Use a setup machine for smoke.

---

## Hosts

| Host | Role | DB | Purpose |
|------|------|----|---------|
| `saas.localhost` | **Central** (landlord) | central SaaS | Super admin: tenants, domains, activate/deactivate (`Modules/Saas`) |
| `lab.localhost` | Tenant | tenant DB for `lab` | Manual day-to-day SPA + Core staff auth |
| `*.localhost` | Tenant | that slug’s DB | First DNS label = tenant slug |

Optional Complere-style dots (later): `lab.local.mountain.local`, `saas.local.mountain.local`.
Parser already understands both schemes in `utils/tenantHost.ts`.

- **Central host is not a tenant.** Tenant Core routes must not be called from `saas.*`.
- **Tenant host** resolves Domain → Tenant → tenant connection (Stancl / Inventory pattern).

---

## One-time machine setup (setup machine only)

### 1. /etc/hosts

```bash
echo "127.0.0.1  lab.localhost saas.localhost" | sudo tee -a /etc/hosts
```

### 2. Compose / nginx

Frontend and Laravel share a cookie domain via same-origin proxy:

- Browser → `http://lab.localhost` (or published port)
- nginx: `/api` + `/sanctum` → Laravel
- `NUXT_PUBLIC_API_BASE_URL` empty → SPA uses `window.location.origin`

### 3. Verify tenant Core is up

```bash
# Expect 401 JSON without session — means Host resolved and route exists
curl -s -o /dev/null -w "%{http_code}\n" -H "Host: lab.localhost" http://127.0.0.1/api/auth/me
```

SaaS central (expect route under `/api/saas/...`):

```bash
curl -s -o /dev/null -w "%{http_code}\n" -H "Host: saas.localhost" http://127.0.0.1/api/saas/auth/me
```

---

## SPA commands

```bash
cd frontend
pnpm install          # or npm — match lockfile
pnpm run dev          # open via lab.localhost (not bare IP if cookies matter)
```

Login page: `/login` → `POST /api/auth/login` after Sanctum CSRF.

---

## Seeded staff (when DevPilotSeeder / tenant seed exists)

| Role | Notes |
|------|--------|
| Owner | Full settings |
| Manager | Outlet-scoped |
| Front desk / Cashier / … | PDF MVP roles |
| Drivers | **No login** — printed passenger list only |

Exact emails/passwords live in the Laravel seeder on the setup machine — do not commit secrets here.

---

## Auth cookie flow (must work for login)

1. Browser loads SPA on `lab.localhost`.
2. First unsafe call (or `API` init) → `GET /sanctum/csrf-cookie` → sets `XSRF-TOKEN` + session cookie.
3. `POST /api/auth/login` with `X-XSRF-TOKEN` + `credentials: include`.
4. Later navigations → `GET /api/auth/me` via `useAuthStore().saveUser()`.

If login “succeeds” but refresh logs out: cookie `SameSite` / wrong Host / API on another origin without CORS credentials.

---

## Related docs

- Deep API/auth map: [`EQMS_SPA_API_AUTH.md`](./EQMS_SPA_API_AUTH.md)
- Backend Module 1 work item: `work/inprogress/71-laravel-modules-port.md`
