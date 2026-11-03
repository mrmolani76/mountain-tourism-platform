# Module A — Todo list (Phase 1 design)

- **Phase**: 1 Foundation
- **Module**: A — Core and super admin
- **PDF**: §5A, §3, §4
- **Focus**: backend / API only (Django + DRF)
- **Phase doc**: [`../phases/01-foundation.md`](../phases/01-foundation.md)
- **Module doc**: [`../../docs/modules/A-core-and-super-admin.md`](../../docs/modules/A-core-and-super-admin.md)
- **Note**: Historical design for kanban 02–10 (completed). Active Module 1 closeout = `29`–`33` in this folder.
- **Done when**: two tenants cannot see each other’s data (automated test)

Status legend: `[ ]` pending · `[~]` in progress · `[x]` done

---

## Dependency order

```text
02 Architecture
 └─ 03 Django+DRF scaffold
      ├─ 04 Tenant isolation + test
      │    └─ 05 Auth / roles / outlet access / password reset
      │         ├─ 06 Buildings / outlets / seasons / settings
      │         │    └─ 10 Foundation seed (§2 buildings)
      │         └─ 07 Super admin APIs
      ├─ 08 VPS setup guide (API)
      └─ 09 GitHub Actions (API)
```

---

## TODO-02 — Architecture note + data model  
**Kanban**: `work/todo/02-m1-architecture-data-model.md` · area: docs · effort: M

- [ ] Read PDF §3–§5A + `docs/GLOSSARY.md` + `docs/TECHNICAL_RULES.md`
- [ ] Fill `docs/ARCHITECTURE.md` (API-first Django layout, no Nuxt)
- [ ] Choose tenant isolation: scoped manager **or** Postgres RLS → row in `docs/DECISIONS.md`
- [ ] Choose auth style for API (session vs token) → `docs/DECISIONS.md`
- [ ] Entity list: Tenant, TenantSettings, Staff, StaffOutletAccess, Building, Outlet, Season, Audit stub
- [ ] Suggested app packages: `modules.core` / `apps.accounts`
- [ ] Open questions closed or explicitly listed
- [ ] Change report + PLAN/SUMMARY

**Acceptance**: architecture note review-ready; isolation + auth decisions recorded.

---

## TODO-03 — Django + DRF scaffold  
**Kanban**: `work/todo/03-m1-django-drf-scaffold.md` · area: backend · effort: M · depends: 02

- [ ] Create `backend/` Django project
- [ ] Install DRF; project settings split (base/local/prod pattern OK)
- [ ] PostgreSQL via env vars
- [ ] `.env.example` with every var explained
- [ ] Health endpoint (e.g. `GET /api/health/`)
- [ ] OpenAPI/Swagger stub enabled
- [ ] Basic `README` section: how to run API locally
- [ ] `.gitignore` for Python/venv/env
- [ ] **No** `frontend/` scaffold in this item
- [ ] Change report + PLAN/SUMMARY

**Acceptance**: API boots against Postgres; health returns OK; env example complete.

---

## TODO-04 — Tenant isolation + cross-tenant test  
**Kanban**: `work/todo/04-m1-tenant-isolation.md` · area: backend/tests · effort: M · depends: 03

- [ ] `Tenant` model (`name`, `is_active`, timestamps)
- [ ] Base model / mixin with `tenant_id` (FK) for tenant-owned tables
- [ ] **Single** isolation layer (manager / queryset / RLS) used everywhere
- [ ] Document how request gets tenant (from authenticated staff)
- [ ] Reject/ignore client-sent `tenant_id` for authz
- [ ] Test: create Tenant A + B data; A credentials cannot read B rows (list + detail)
- [ ] Test documented as Module A “done when” proof
- [ ] Change report + PLAN/SUMMARY

**Acceptance**: automated cross-tenant test green; isolation approach matches DECISIONS.

---

## TODO-05 — Auth, roles, outlet access, password reset  
**Kanban**: `work/todo/05-m1-auth-roles-api.md` · area: backend · effort: M · depends: 04

- [ ] Staff/User model: email, password, role, tenant_id, is_active
- [ ] Roles enum aligned with PDF §3 (at least: super_admin, owner, manager, front_desk, cashier, rental_desk, service_staff, transport_desk, lift_operator)
- [ ] Login API (email + password)
- [ ] Logout / session-or-token invalidate as chosen
- [ ] `GET /api/me/` — identity, role, tenant, outlets
- [ ] `StaffOutletAccess` (M2M or table)
- [ ] Owner **and** manager can set staff password (**no email**)
- [ ] Inactive staff / inactive tenant cannot use API
- [ ] Permission helpers for later modules (role checks)
- [ ] Tests: 401 unauthenticated; 403 wrong role; password reset authz
- [ ] OpenAPI updated
- [ ] Change report + PLAN/SUMMARY

**Acceptance**: login works; tenant bound on user; password reset without mail; tests green.

---

## TODO-06 — Buildings, outlets, seasons, settings APIs  
**Kanban**: `work/todo/06-m1-buildings-outlets-seasons-api.md` · area: backend · effort: M · depends: 05

### Settings
- [ ] TenantSettings: currency, tax_rates structure, timezone, units (metric/imperial)
- [ ] GET/PATCH settings (owner)

### Buildings
- [ ] Building: type, name, altitude_m, map_position / lat-lng
- [ ] CRUD tenant-scoped (owner)
- [ ] Ready to act as weather point later (coords + altitude)

### Outlets
- [ ] Outlet: building_id, name, type (restaurant, hut_kitchen, shop, rental, spa, sports, transport, lifts, …)
- [ ] CRUD tenant-scoped (owner)

### Seasons
- [ ] Season: kind winter|summer|closed, starts_on, ends_on
- [ ] CRUD tenant-scoped (owner)
- [ ] Overlap rule documented (allow/forbid)

### Staff admin (owner)
- [ ] CRUD staff under tenant
- [ ] Assign role + outlet access

### Tests / docs
- [ ] Cross-tenant denial on each resource
- [ ] OpenAPI updated
- [ ] Change report + PLAN/SUMMARY

**Acceptance**: owner can configure complex core data via API; isolation holds.

---

## TODO-07 — Super admin APIs  
**Kanban**: `work/todo/07-m1-super-admin-api.md` · area: backend · effort: M · depends: 05

- [ ] Super-admin-only permission
- [ ] POST create complex (tenant) + owner account (atomic)
- [ ] Default TenantSettings on create
- [ ] Activate complex
- [ ] Deactivate complex (staff of that tenant blocked afterward)
- [ ] Non–super-admin → 403 on all super routes
- [ ] Tests for create / activate / deactivate / 403
- [ ] OpenAPI updated
- [ ] Change report + PLAN/SUMMARY

**Acceptance**: super admin can provision and toggle complexes safely.

---

## TODO-08 — VPS setup guide for API  
**Kanban**: `work/todo/08-m1-vps-api-setup.md` · area: devops · effort: M · depends: 03

- [ ] Doc/script: dedicated Linux user
- [ ] Postgres DB + user
- [ ] Nginx → gunicorn/uvicorn
- [ ] systemd unit; restart on crash/reboot
- [ ] `.env` on server only
- [ ] sslip.io or port; later domain = Nginx + env only
- [ ] Explicit **no Docker** MVP path
- [ ] Do not touch other sites on VPS
- [ ] Change report + PLAN/SUMMARY

**Acceptance**: fresh VPS can be prepared from the guide without us.

---

## TODO-09 — GitHub Actions (backend)  
**Kanban**: `work/todo/09-m1-github-actions-api.md` · area: devops · effort: L · depends: 03

- [ ] PR workflow: install, lint, test (backend)
- [ ] Failed check blocks merge (documented)
- [ ] Main: build on GitHub; SSH release folders on VPS
- [ ] Migrate; switch release; reload; health check
- [ ] Auto-rollback on failed health
- [ ] Keep last N releases
- [ ] Secrets only in GitHub Secrets
- [ ] Change report + PLAN/SUMMARY

**Acceptance**: CI path defined/implemented for API; no secrets in repo.

---

## TODO-10 — Foundation seed (§2 buildings, partial)  
**Kanban**: `work/todo/10-m1-foundation-seed.md` · area: backend · effort: S · depends: 06

- [ ] Management command or seed script
- [ ] Demo tenant + buildings from PDF §2:

| Building | Altitude |
|----------|----------|
| Valley station | 1,050 m |
| Main hotel | 1,200 m |
| Chalets | 1,250 m |
| Mid station | 1,800 m |
| Mountain hut | 2,100 m |
| Summit station | 2,600 m |

- [ ] Sample outlets in key buildings (minimal)
- [ ] Idempotent or documented reset
- [ ] Dev-only safety notes
- [ ] Change report + PLAN/SUMMARY

**Acceptance**: seed creates §2 layout for local/API tests; full seed still Phase 5.

---

## Module A exit checklist (phase gate)

- [ ] All TODO-02 … TODO-10 done (or deferred with written reason)
- [ ] Cross-tenant automated test green (PDF done when)
- [ ] OpenAPI covers shipped Module A endpoints
- [ ] Architecture note delivered
- [ ] VPS + CI path documented for API
- [ ] No frontend work slipped in
- [ ] Ask user: commit aur push?

---

## Out of scope (do not add to this list)

Modules B–I features, Nuxt UI, email sending, subdomain tenancy, §9 after-MVP.
