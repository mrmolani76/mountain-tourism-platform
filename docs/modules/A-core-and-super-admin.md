# Module A — Core and super admin

- **PDF**: §5A, §3 roles, §4 Tenant/Building/Outlet/Season
- **Phase**: 1 ([`work/phases/01-foundation.md`](../../work/phases/01-foundation.md))
- **Kanban**: 02–10
- **Backend app**: `apps.core` (+ thin `apps.accounts` if split)

## 1. Purpose

Foundation for every other module: who is logged in, which complex (tenant) they belong to, buildings/outlets, seasons, settings, and super-admin provisioning. Prove **tenant isolation by automated test**.

## 2. Actors

| Role | API capabilities (Phase 1) |
|------|----------------------------|
| Super admin | Create/deactivate tenants + owner accounts; not bound to one tenant |
| Owner | Settings, buildings, outlets, staff, seasons (full tenant) |
| Manager | Later outlet ops; Phase 1 may read assigned outlets |
| Other roles | Exist in enum; feature APIs in later modules |

Password reset: **Owner/Manager** reset staff passwords themselves. **No email** in MVP.

## 3. Domain model (backend)

### Tenant (complex)
- `id`, `name`, `is_active`, timestamps
- Owner user link
- Settings: `currency`, `tax_rates` (structure TBD), `timezone`, `units` (`metric` °C/km/h vs `imperial` °F/mph)

### Staff / User
- Belongs to **one** `tenant_id` (null only for super admin if modeled that way)
- `email`, password hash, `role`, `is_active`
- M2M or explicit `StaffOutletAccess` → outlets

### Building
- `tenant_id`, `name`, `type`, `altitude_m`, `map_position` (lat/lng or x/y — decide in architecture), coordinates for weather later
- Every building is also a **weather point** (Module H reuses)

### Outlet
- `tenant_id`, `building_id`, `name`, `type` (restaurant, hut_kitchen, shop, rental, spa, sports, transport, lifts, …)
- Own products/staff/reports later

### Season
- `tenant_id`, `kind` (winter | summer | closed), `starts_on`, `ends_on`
- Prices/menus/services differ per season (consumed by B–F)

### Audit (foundation)
- Append-only audit for charge/payment/void events (models ready in Phase 1; writers in Module C+)

## 4. Business rules (deep)

1. **Tenant from login** — no subdomain resolution in MVP; staff’s tenant is fixed on the account.
2. **Inactive tenant** — staff of deactivated complex cannot authenticate successfully (or cannot access APIs).
3. **Isolation** — all tenant queries go through one scoped layer (manager/RLS). Never trust client-sent `tenant_id` for authz.
4. **Super admin** — only role that creates tenants; never used for day-to-day outlet ops.
5. **Outlet access** — cashier/rental/etc. later blocked if outlet not assigned; enforce on API.
6. **Seasons** — date ranges should not overlap ambiguously for same kind without explicit rule (document choice: allow overlap or forbid).
7. **Settings** — “today”, nightly jobs, reports use **tenant timezone** (Phase 2+).

## 5. API surface (sketch)

| Method | Path (example) | Who | Notes |
|--------|----------------|-----|-------|
| POST | `/api/auth/login/` | public | email/password |
| POST | `/api/auth/logout/` | staff | if session auth |
| POST | `/api/auth/password-reset/` | owner/manager | target staff id + new password |
| GET/PATCH | `/api/me/` | staff | profile, role, outlets |
| POST | `/api/super/tenants/` | super | create complex + owner |
| POST | `/api/super/tenants/{id}/activate/` | super | |
| POST | `/api/super/tenants/{id}/deactivate/` | super | |
| CRUD | `/api/buildings/` | owner | tenant-scoped |
| CRUD | `/api/outlets/` | owner | |
| CRUD | `/api/seasons/` | owner | |
| GET/PATCH | `/api/settings/` | owner | |
| CRUD | `/api/staff/` | owner | role + outlet access |

Exact paths fixed during scaffold; keep REST + OpenAPI.

## 6. Services / internals

- `TenantContext` middleware or auth hook sets current tenant
- `TenantQuerySet` / manager: `.for_tenant(tenant)`
- Password set service (no mailer)
- SuperAdminTenantProvisioner: atomic create tenant + owner + default settings

## 7. Test scenarios (must)

1. Tenant A staff cannot GET Tenant B building by id (404/403).
2. List endpoints never leak other tenant rows.
3. Deactivated tenant login denied.
4. Non-super cannot call `/api/super/*`.
5. Manager cannot reset password if policy says only owner (define; PDF: owners **and** managers).

## 8. Done when (PDF)

Two complexes on the same server cannot see each other's data, proven by an **automated test**, not clicking around.

## 9. Dependencies

- None (first module)
- Unlocks all other modules

## 10. Explicit non-goals

- Email verification / magic links
- Subdomain per tenant
- Frontend admin UI (deferred)
- Full audit writers for money (until C)

## 11. Implementation checklist

- [ ] Architecture + model draft (work 02)
- [ ] Django scaffold (03)
- [ ] Isolation + test (04)
- [ ] Auth/roles (05)
- [ ] Buildings/outlets/seasons/settings (06)
- [ ] Super admin APIs (07)
- [ ] VPS + CI for API (08–09)
- [ ] Partial §2 seed (10)
