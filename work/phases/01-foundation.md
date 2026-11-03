# Phase 1 — Foundation

- **PDF**: Milestone 1 (§8)
- **Weeks**: 1–2
- **Payment**: 15%
- **Modules in this phase**: **A only** (complete description below)
- **Depends on**: none
- **Kanban**: `work/todo/` 02–10
- **Module todo list (design, historical)**: [`work/todo/A-core-and-super-admin.md`](../todo/A-core-and-super-admin.md)
- **Focus**: backend / API only (Django + DRF). UI deferred.
- **Deep doc**: [`docs/modules/A-core-and-super-admin.md`](../../docs/modules/A-core-and-super-admin.md)

---

## Phase goal

Stand up the API foundation on which every later module depends: tenancy, auth, buildings/outlets/seasons/settings, super-admin provisioning, VPS+CI for the API, architecture note.

**Payment unlocks when** this runs on the client VPS and Module A “done when” passes.

---

## Modules in this phase

### Module A — Core and super admin (COMPLETE)

**PDF**: §5A, §3 roles, §4 Tenant / Building / Outlet / Season

#### Purpose
One complex = one **Tenant**. Staff login knows the tenant (no subdomains). Super admin creates complexes. Owners configure buildings, outlets, staff, seasons, and locale settings. This module must prove **automated tenant isolation**.

#### Actors (this phase)

| Role | What they do via API |
|------|----------------------|
| Super admin | Create complex + owner; activate/deactivate complex |
| Owner | Buildings, outlets, seasons, settings, staff |
| Manager | May reset staff passwords (PDF); outlet ops mainly later |
| Other roles | Enum present; feature APIs in later phases |

Drivers do not log in (PDF). No email sending for password reset in MVP — owner/manager sets password directly.

#### Entities to implement

| Entity | Key fields / notes |
|--------|--------------------|
| Tenant | name, is_active; one complex |
| TenantSettings | currency, tax rates, timezone, units (°C/km/h or °F/mph) |
| Staff/User | email, password, role, tenant_id (super admin special), is_active |
| StaffOutletAccess | which outlets a staff member may use |
| Building | type, altitude, map position; also future weather point |
| Outlet | in a building; type: restaurant, hut kitchen, shop, rental, spa, sports, transport, lifts, … |
| Season | winter / summer / closed + dates |
| Audit foundation | ready for charge/payment/void writers (Module C+) |

#### Business rules (must enforce in API)

1. Every business table carries `tenant_id`.
2. Tenant isolation in **one place** (scoped manager or Postgres RLS) — not copy-paste filters in every view.
3. Never trust client-supplied `tenant_id` for authorization.
4. Deactivated tenant → staff cannot use the API.
5. Super admin is the only cross-tenant operator.
6. Permissions checked on the API, not only hidden in a future UI.
7. “Today” / nightly jobs / reports later use **tenant timezone** (settings here).

#### API surface (this phase)

- Auth: login, logout (if session), me, staff password reset (owner/manager)
- Super: create tenant+owner, activate, deactivate
- CRUD: buildings, outlets, seasons, staff (+ outlet access)
- GET/PATCH: tenant settings
- OpenAPI for all shipped endpoints

#### Services

- Tenant context from authenticated user
- ProvisionTenant (atomic tenant + owner + default settings)
- PasswordSet (no mailer)

#### Acceptance — Module A done when (PDF)

> Two complexes on the same server cannot see each other's data, proven by an **automated test**, not by clicking around.

Extra Phase 1 gates: architecture note delivered; API deployable per §6 path (script/CI).

#### Explicitly NOT in Module A / Phase 1

Rooms, folio, POS, rental, services, transport, lifts, weather, dashboard, Nuxt UI, §9 after-MVP.

#### Suggested backend package

`modules.core` (+ `apps.accounts` if split)

#### Work packages (kanban)

Full checkbox design (historical): [`work/todo/A-core-and-super-admin.md`](../todo/A-core-and-super-admin.md)

| ID | Package | Kanban file |
|----|---------|-------------|
| 02 | Architecture note + data model draft | `work/todo/02-m1-architecture-data-model.md` |
| 03 | Django + DRF scaffold | `work/todo/03-m1-django-drf-scaffold.md` |
| 04 | Tenant isolation + cross-tenant test | `work/todo/04-m1-tenant-isolation.md` |
| 05 | Auth / roles / outlet access / password reset API | `work/todo/05-m1-auth-roles-api.md` |
| 06 | Buildings, outlets, seasons, settings APIs | `work/todo/06-m1-buildings-outlets-seasons-api.md` |
| 07 | Super admin APIs | `work/todo/07-m1-super-admin-api.md` |
| 08 | VPS setup guide for API | `work/todo/08-m1-vps-api-setup.md` |
| 09 | GitHub Actions backend lint/test/deploy | `work/todo/09-m1-github-actions-api.md` |
| 10 | Partial §2 building seed | `work/todo/10-m1-foundation-seed.md` |

---

## Phase-level out of scope

Modules B–I, all frontend screens, §9 product expansions.

## Hard constraints (§6 / §7) for this phase

- No Docker required on VPS; own user/DB/Nginx/systemd
- Secrets in `.env` only
- Free stack; PostgreSQL
- Web API only (no native apps)
