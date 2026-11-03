# Mountain SPA ↔ eQMS qualitycenter-spa — API & auth map

Deep reference for how this frontend mirrors **eQMS `qualitycenter-spa`**.
Source inspected: `/var/www/html/eQMS/qualitycenter-spa` (+ `complere/LOCAL_SETUP.md`).

Mountain stack note: eQMS uses **axios**; this SPA keeps **`$fetch` / ofetch** (no new npm deps).
Behaviour and folder layout match eQMS; the HTTP library differs on purpose.

---

## 1. Non-negotiable SPA rules (from eQMS CLAUDE.md)

| Rule | eQMS | Mountain |
|------|------|----------|
| No raw HTTP in pages | Pages never call `$fetch` / axios | Same — pages → domain service → `API` |
| Domain services | `composables/{domain}/*Service.ts` | `composables/auth/useAuthService.ts`, later `composables/hotel/…` |
| Shared HTTP choke point | `composables/core/useApi.ts` → `export const API` | Same path + name |
| Auth store | `stores/useAuthStore.ts` | `stores/authStore.ts` + alias `useAuthStore` |
| One global route guard | `middleware/auth.global.ts` | Same |
| CASL / role lists are UI-only | Server policies win | Same — `ROLE_PERMISSIONS` are menu hints |
| Host decides tenant vs SaaS | `utils/common.ts` | `utils/tenantHost.ts` |

---

## 2. Host → tenant / SaaS (eQMS `utils/common.ts`)

### eQMS (Complere)

```
{slug}.{env}.complere.tech     prod-style
{slug}.local.complere.local    local
saas.*                         CENTRAL admin (not a tenant)
```

| Export | Meaning |
|--------|---------|
| `SAAS_CUSTOMER` | Tenant slug, or `saas` for central |
| `SAAS_CUSTOMER_URL` | `https://{identifier}{.complere.local\|.complere.tech}` |
| `IS_SAAS_ADMIN_HOST` | `SAAS_CUSTOMER === 'saas'` |
| API base | `{SAAS_CUSTOMER_URL}/backend/api/` |
| CSRF | `{SAAS_CUSTOMER_URL}/backend/sanctum/csrf-cookie` |

On the **saas** host, tenant routes 404 by design. Only `/saas/*` (central) exists.
`useApi` 401 on that host must **not** touch the tenant auth store / login dialog.

### Mountain (Module 1)

| Host | Role | API |
|------|------|-----|
| `lab.localhost` (or `lab.local.mountain.local`) | Tenant `lab` | `/api/*` Core (staff) |
| `saas.localhost` (or `saas.local.mountain.local`) | Central SaaS | `/api/saas/*` |
| bare `localhost` | Dev default → treat as `lab` | Core |

Helpers: `frontend/utils/tenantHost.ts`

| Export | Meaning |
|--------|---------|
| `TENANT_SLUG` | First label, or `null` on saas host |
| `IS_SAAS_ADMIN_HOST` | `slug === 'saas'` |
| `resolveApiOrigin()` | `NUXT_PUBLIC_API_BASE_URL` or `window.location.origin` |
| API base | `{origin}/api/` (nginx same-origin; no `/backend` prefix) |
| CSRF | `{origin}/sanctum/csrf-cookie` |

Empty `NUXT_PUBLIC_API_BASE_URL` = same-origin (Docker nginx proxies `/api` + `/sanctum` to Laravel). That is the preferred local shape, matching eQMS same-origin cookie sessions.

---

## 3. `useApi` — Sanctum CSRF + credentials

### eQMS flow (`composables/core/useApi.ts`)

1. If cookie `XSRF-TOKEN` missing → GET `/backend/sanctum/csrf-cookie` (promise-cached).
2. Create axios instance:
   - `baseURL = {SAAS_CUSTOMER_URL}/backend/api/`
   - `withCredentials: true`
   - header `X-XSRF-TOKEN` from cookie
3. Methods: `get` / `post` / `put` / `patch` / `delete`
4. Central `errorHandler`: 401 → unauthenticate + login dialog (tenant) or saasAdmin login (central); 403/422/429/… toasts / redirects

### Mountain flow (`composables/core/useApi.ts`)

1. Same CSRF cookie check + **cached promise** (`csrfTokenPromise`).
2. `$fetch` to `{resolveApiOrigin()}/api/{path}` with `credentials: 'include'` + `X-XSRF-TOKEN`.
3. Same method names; singleton `export const API = new MountainApi()`.
4. 401: tenant → `authStore.unauthenticate()` + optional login dialog; saas host → `/saas/login` (pages land later).
5. FormData posts skip forced `Content-Type` so the browser sets the multipart boundary.

**Do not** add axios unless product explicitly approves a new dependency.

---

## 4. Auth service + store

### eQMS

| Piece | Path | Job |
|-------|------|-----|
| Service | `composables/auth/useAuthService.ts` | `login`, `logout`, `getUserData`, 2FA, password reset, logo |
| Store | `stores/useAuthStore.ts` | `saveUserData`, permissions, `authenticate` / `unauthenticate` |
| Login page | calls `useAuthService().login` then store hydrate | |

Login POST path: `login` (relative to `/backend/api/`).
Session probe: `GET user`.

### Mountain (Module 1 Core)

| Piece | Path | Job |
|-------|------|-----|
| Service | `composables/auth/useAuthService.ts` | `login`, `logout`, `getUserDetails`, menu stub, reset helpers |
| Store | `stores/authStore.ts` (`useAuthStore` alias) | `saveUser`, `logoutUser`, permissions, dialog |
| Login page | `pages/login.vue` → `useAuthService` + `useAuthStore` | |

| Action | Laravel route (Core) |
|--------|----------------------|
| Login | `POST /api/auth/login` |
| Logout | `POST /api/auth/logout` |
| Me | `GET /api/auth/me` (middleware `staff.auth`) |
| Reset password | `POST /api/auth/reset-password` |

Staff JSON (`AuthController::mePayload`) maps into Vuexy `User` via `mapStaffToUser`.
UI permission list is `ROLE_PERMISSIONS[role_code]` — **not** a substitute for server AuthZ.

Central SaaS auth (later): `POST /api/saas/auth/login` etc. Must use `IS_SAAS_ADMIN_HOST` and must not call tenant `auth/me`.

---

## 5. Call-chain diagram

```
Page / Dialog / Middleware
        │
        ▼
useAuthService  (or other domain *Service / use*ApiClient)
        │
        ▼
API  (composables/core/useApi)     ←── only place that touches network
        │
        ├── GET  {origin}/sanctum/csrf-cookie   (once if no XSRF-TOKEN)
        └── *    {origin}/api/{path}            (credentials + X-XSRF-TOKEN)
                │
                ▼
        Laravel Modules/Core or Modules/Saas
```

Legacy: `utils/api.ts` still exports `$api` and `ensureLaravelCsrfCookie` for older clients.
New code should import `{ API }` or use a domain service.

---

## 6. Middleware (`auth.global.ts`)

Same idea as eQMS:

1. Public pages skip session probe (except `unauthenticatedOnly` guest pages).
2. First navigation with unknown session → `store.saveUser()` → `GET auth/me`.
3. Logged-in users bounce off `/login`.
4. Protected routes without session → `/login?to=…`.
5. Optional `meta.checkPermission` is **UI-only**; server 403 is authoritative.

SaaS-admin host branching (eQMS `IS_SAAS_ADMIN_HOST` + `/saasAdmin/*`) is deferred until Module 1 SaaS pages land. Host detection already exists in `tenantHost.ts`.

---

## 7. Local hosts (mirror of eQMS LOCAL_SETUP)

See also: `frontend/docs/LOCAL_SETUP.md`.

eQMS uses Apache + `lab.local.complere.local`. Mountain Docker / nginx (on the setup machine) should expose:

| Host | Purpose |
|------|---------|
| `lab.localhost` | Primary tenant for SPA `npm run dev` / compose frontend |
| `saas.localhost` | Super-admin portal (central DB) |

`/etc/hosts` example:

```text
127.0.0.1  lab.localhost saas.localhost
```

Verify (setup machine only):

```bash
curl -s -o /dev/null -w "%{http_code}\n" -H "Host: lab.localhost" http://127.0.0.1/api/auth/me
# 401 without cookie is OK — proves tenant Core is listening
```

---

## 8. What is intentionally different

| Topic | Why |
|-------|-----|
| ofetch vs axios | No new package without approval |
| No `/backend` prefix | Mountain nginx maps `/api` and `/sanctum` directly |
| No 2FA / terms / digital signature | Out of MVP scope (eQMS regulated QMS) |
| Drivers | No SPA login — printed passenger list (PDF) |
| Django `$api` CSRF fallback | Kept briefly while DRF still exists; prefer Laravel XSRF |

---

## 9. Checklist for the next domain module

When adding Hotel / Folio / … SPA clients:

1. Add `composables/{domain}/use{Domain}Service.ts` (or keep `api-clients` until renamed).
2. Call `API.get/post/...` only — never `$fetch` from a page.
3. Do not invent client-only money rules (voids, board plans).
4. Keep tenant Host resolution; never hardcode another complex’s slug into URLs.
