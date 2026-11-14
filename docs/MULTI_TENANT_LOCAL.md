# Multi-tenant local model — EQMS, Nexro, Mountain Tourism

**Purpose:** One place that separates **central (SaaS)** vs **tenant (complex/shop)** the way EQMS and Nexro do it.

**Not this doc:** Apache install, Docker runbooks, or machine setup. See `APACHE_LOCAL_SETUP.md`, `LARAVEL_LOCAL_SETUP.md`, Nexro `docs/DOCKER_SETUP.md`.

**Product decision:** Mountain Tourism = **DB-per-tenant** + **Host → Domain** resolve. No `tenant_id` on business rows. (`docs/DECISIONS.md`)

---

## 1. Shared idea (all three)

| Layer | What | Isolated how |
|-------|------|----------------|
| **Central / landlord** | Tenants registry, domains, SaaS admins | Own DB + **central host(s)** only |
| **Tenant** | Business data (staff, folio, inventory, QMS, …) | **Own physical database** per tenant |
| **Resolve** | Which DB for this request? | **HTTP `Host`** → `domains` row → activate tenant connection |

```text
Browser ──Host: lab.…──► API
                            │
                            ├─ Host matches central_domains / saas.*
                            │     → central DB only (SaaS APIs)
                            │
                            └─ Host matches tenant Domain
                                  → switch connection to that tenant DB
```

**Separation rules**

1. Central host is **never** a tenant. Tenant routes on central host → 404 / reject.
2. Tenant host must not serve SaaS “create complex” as if it were landlord (unless product explicitly designs that).
3. Staff users live in the **tenant** DB. SaaS users live in the **central** DB.
4. Isolation = **active DB connection**, not row-level `tenant_id` filters on business tables.

---

## 2. EQMS / Complere (reference)

Source: Complere `LOCAL_SETUP.md` on the EQMS machine tree.

### Hosts (dot scheme)

| Host | Role | DB |
|------|------|-----|
| `saas.local.complere.local` | Central | Central Complere DB |
| `lab.local.complere.local` | Dev tenant | `lab_local` |
| `phpunit.local.complere.local` | Test tenant | `phpunit_local` |
| `e2e.local.complere.local` | Playwright | `e2e_local` |
| `demo.local.complere.local` | Demo | `demo_local` |

- Tenant host = `{url_prefix}.{env}.complere.local`
- Tenant DB = `{url_prefix}_{env}`
- Same Laravel app; Apache: `DocumentRoot public_nuxt` + `Alias /backend` → `public/`
- Browser: `https://lab.local.complere.local` (API under `/backend/...`)

### What is separated

| Concern | Central | Tenant |
|---------|---------|--------|
| Users | SaaS / platform | Lab staff (Admin, Manager, …) |
| APIs | `/backend/api/saas/*` | Domain modules on tenant host |
| Data | tenants, domains | All QMS business tables |
| Provision | `resetLocalDatabase.sh` | Creates/resets named tenant DBs |

---

## 3. Nexro / Inventory (reference)

Sources: Nexro `CLAUDE.md`, `docs/DOCKER_SETUP.md`, stancl/tenancy.

### Hosts (subdomain scheme)

| Host | Role |
|------|------|
| `nexro.local` (or `CENTRAL_DOMAIN`) | Central / SaaS admin SPA |
| `shop1.nexro.local`, `shop2.nexro.local`, … | Tenant shops |

- Package: **stancl/tenancy** — domain (or subdomain) initialize middleware
- Tenant DB: separate DB per tenant (often `tenant_` prefix)
- Routes: central `routes/api.php` vs tenant `routes/tenant.php`
- Local often Docker; `hosts:sync` can maintain hosts for new shops

### What is separated

| Concern | Central | Tenant |
|---------|---------|--------|
| Users | Platform admins | Shop users / permissions |
| Data | tenants, domains, SaaS | customers, products, invoices, … |
| Cache / queue / files | Bootstrappers scope per tenant | Same |

---

## 4. Mountain Tourism (this repo) — target separation

Align with EQMS + Nexro **ideas**, product names from `CLAUDE.md`.

### Hosts (current local names)

| Host | Role | DB (local example) |
|------|------|---------------------|
| `saas.localhost` | Central (super admin) | Central DB (e.g. `mountain_tourism`) |
| `lab.localhost` | Pilot complex (tenant) | `lab_local` |

Optional later (EQMS-like dots): `lab.local.mountain.local` — only with explicit hosts agreement.

Config hints: `TENANCY_CENTRAL_HOSTS`, `CENTRAL_DOMAIN`, `central_host_pattern` = `/^saas\./`  
(`backend-laravel/config/tenancy.php`)

### What must stay separate

| Concern | Central (`saas.*`) | Tenant (`lab.*`) |
|---------|--------------------|------------------|
| Auth users | `SaasUser` | `core_staff` |
| Registry | `Tenant`, `Domain` | — |
| Business | — | Hotel, Folio, POS, Rental, Services, Transport, Weather, Reports |
| Migrations | Saas + app framework tables | `Modules/*/Database/migrations/tenant/` |
| Login URL | SaaS panel host | Tenant host `/login` |
| Fixture (local) | `super@saas.test` | `owner@lab.test` / `password123` |

### Resolve flow (Laravel)

1. Read `Host`
2. If central host / `saas.*` → central connection only
3. Else lookup `Domain.hostname` on central → `Tenant.db_name`
4. Activate tenant connection (`TenantConnection::activate` or stancl bootstrapper)
5. Never query another tenant’s DB from tenant APIs

### Implemented route / middleware split (2026-11-14)

EQMS/Nexro-style separation using the custom `App\Tenancy` bridge (full stancl HTTP init deferred):

| Surface | Middleware / binding |
|---------|----------------------|
| SaaS (`Modules/Saas`) | `Route::domain` per `tenancy.central_domains` + `tenancy.prevent_tenant` |
| Tenant modules | `tenancy.prevent_central` → `resolve.tenant` → `tenancy.ensure_resolved` |
| Health `/api` + `/up` | No tenancy middleware |
| Aliases | `tenancy.prevent_central`, `tenancy.prevent_tenant`, `tenancy.ensure_resolved`, `resolve.tenant` |

Global `ResolveTenant` is **not** prepended on all `api` routes (so central SaaS does not activate a tenant DB).

### Provision commands (reference only — not run by this doc)

```bash
# Central migrate (explicit paths — do not dump tenant module migrations into central)
php artisan migrate --path=database/migrations --force
php artisan migrate --path=Modules/Saas/Database/migrations --force

# Tenant DB + registry + staff
php artisan tenants:provision-local lab --with-saas-admin --reset
```

DB name convention: `{slug}_local` (e.g. `lab_local`), same idea as EQMS `{prefix}_{env}`.

---

## 5. Side-by-side

| | EQMS | Nexro | Mountain Tourism |
|--|------|-------|------------------|
| Isolation | DB-per-tenant | DB-per-tenant (stancl) | DB-per-tenant |
| Central host | `saas.local.complere.local` | `nexro.local` | `saas.localhost` |
| Tenant host | `lab.local.complere.local` | `shop1.nexro.local` | `lab.localhost` |
| Tenant DB name | `lab_local` | `tenant_*` / provisioned | `lab_local` |
| API path habit | `/backend/api/...` | `/api/...` | `/api/...` |
| Staff vs SaaS | Separated | Separated | Separated |

---

## 6. Do / Do not (local multi-tenant)

**Do**

- Use **tenant host** for staff login and desk workflows
- Use **central host** only for complex create / activate / SaaS admin
- Keep one physical DB per complex
- Prefer automated tests that prove two complexes cannot see each other’s data

**Do not**

- Put business tables only on the central DB
- Rely on `tenant_id` columns instead of connection switching
- Point SPA `apiBaseUrl` at the wrong host (cookie / tenancy break)
- Add `/etc/hosts` rows unless the human explicitly asks
- Run all module **tenant** migrations into the central database by accident (`migrate` without `--path`)

---

## 7. Related docs

| Doc | Scope |
|-----|--------|
| `docs/DECISIONS.md` | Why DB-per-tenant |
| `docs/ARCHITECTURE.md` | High-level Host → DB |
| `docs/DATABASE_SCHEMA.md` | Central vs tenant tables |
| `docs/APACHE_LOCAL_SETUP.md` | Hosting / vhosts (setup) |
| `docs/LARAVEL_LOCAL_SETUP.md` | Artisan / DB local (setup) |
| EQMS Complere `LOCAL_SETUP.md` | Complere host matrix |
| Nexro `docs/DOCKER_SETUP.md` | Shop subdomain + hosts |

---

## 8. Path note (`/var/www/html`)

Multi-tenant **rules above do not change** if the clone moves under `/var/www/html` for Apache readability. Hosts and DB names stay the same unless the human asks otherwise. Home directories with mode `750` may block `www-data` from DocumentRoot; that is a filesystem concern, not a tenancy-model change.
