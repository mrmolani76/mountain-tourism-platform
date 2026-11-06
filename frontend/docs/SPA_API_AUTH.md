# Mountain SPA — API & auth map

How the Nuxt frontend talks to the Laravel API.

| Rule | This SPA |
|------|----------|
| No raw HTTP in pages | Pages → domain client/service → `API` |
| Shared HTTP client | `composables/core/useApi.ts` → `export const API` |
| Auth store | `stores/authStore.ts` + alias `useAuthStore` |
| Global route guard | `middleware/auth.global.ts` |
| Role lists are UI-only | Server AuthZ wins; `ROLE_PERMISSIONS` are menu hints |
| Host decides tenant vs SaaS | `utils/tenantHost.ts` |

## Host → tenant / SaaS

```
{slug}.localhost                 local tenant
lab.localhost                    pilot shorthand
saas.localhost                   central admin (not a tenant)
lab.local.mountain.local         optional dotted local form
```

| Export | Meaning |
|--------|---------|
| `resolveTenantSlug()` | Tenant slug from hostname, or `saas` for central |
| `IS_SAAS_ADMIN_HOST` | Central admin host |
| `resolveApiOrigin()` | Same-origin root used for `/api` and `/sanctum` |

## Auth flow

1. `GET /sanctum/csrf-cookie` before mutating calls (when XSRF cookie missing)
2. `POST /api/auth/login`
3. `GET /api/auth/me` on boot (`authStore.saveUser`)
4. `POST /api/auth/logout`

Credentials: `include`. Mutating calls send `X-XSRF-TOKEN`.

## Staff listing

| Method | Path |
|--------|------|
| GET | `/api/users/list/config` |
| POST | `/api/users/list` |
| POST | `/api/users/create` |
| GET | `/api/user/fetch` |
| GET | `/api/user/show` |
| POST | `/api/user/update` |
| GET | `/api/user/formInitialData` |

Row cells use `{ label, type }` (and optional `url` / `color`) for `DataTableCell`.

## Menus

`GET /api/core/menus` → layout vertical nav (`useCoreService.getVerticalMenuData`).
