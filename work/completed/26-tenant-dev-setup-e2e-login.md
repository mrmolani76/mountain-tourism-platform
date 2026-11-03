---
title: Tenant dev setup — physical DB, migrate, owner seed, E2E login verify
id: 26
priority: high
status: completed
milestone: 1
module: infra
area: devops
effort: M
dependencies: 25
bug: none
created: 2026-11-03
updated: 2026-11-03
---

# Tenant dev setup — E2E login

## What to do (What)
Make staff login work end-to-end on this machine:

1. Register tenant `lab` (`lab.local.mountain.local`) on central DB
2. Create physical Postgres tenant DB (`lab_local`)
3. Run migrations on the tenant DB
4. Create owner staff user in the tenant DB
5. `/etc/hosts` entry `127.0.0.1 lab.local.mountain.local`
6. Verify login E2E through SPA nginx proxy (`http://lab.local.mountain.local:3000/api/auth/login/`) and `/api/auth/me/`

User approved this setup on 2026-11-03 (overrides code-only setup policy for this item).

## Current / Before
- SPA renders login page (item 25 / BUG-APP-001 fixed) but staff login via `127.0.0.1:3000` is rejected: `LoginView` requires a tenant host (`TENANCY_CENTRAL_HOSTS`)
- `provision_tenant` registers Tenant + Domain only; physical `createdb`/migrate are stubs (code-only policy)
- No tenant DB exists; no staff user; hosts entry missing

## Expected / After
- `http://lab.local.mountain.local:3000/` serves SPA; login with seeded owner works; `/api/auth/me/` returns the staff profile; session cookie set with SameSite=Lax
- CSRF trusted origins include the tenant host
- Docs (`docs/DOCKER_SETUP.md`) updated with E2E login steps

## Why
User: login page visible hai par login possible nahi; E2E setup approved 2026-11-03.

## Acceptance Criteria (complete only when all ticked)
- [x] Tenant `lab` registered; `lab_local` DB created; migrations applied on tenant alias
- [x] Owner staff user created in tenant DB (credentials printed to console only, not committed)
- [x] `/etc/hosts` has `127.0.0.1 lab.local.mountain.local`
- [x] `POST /api/auth/login/` via `http://lab.local.mountain.local:3000` → 200 + session cookie
- [x] `GET /api/auth/me/` with cookie → 200 with staff payload
- [x] `docs/DOCKER_SETUP.md` updated (E2E login section)
- [x] Change report written; PLAN + SUMMARY updated

## Files Likely Affected
- `docker-compose.dev.yml` (CSRF origins)
- `docs/DOCKER_SETUP.md`

## Notes / Follow-ups
- Dev-only. No production impact. Password printed once; user must change it later via reset-password (owner can reset own staff? no — owners reset staff; dev convenience only)
- Inventory hosts untouched (`/etc/hosts` shared — only append, never remove existing lines)

---

## Return to Pending Log (when work stops midway)

None.

---

## Completion Log (when work is complete)

- **Completed by**: OpenCode agent
- **Date**: 2026-11-03
- **What was done**: Registered tenant `lab` (id=3, db_name `lab_local`); created physical Postgres DB; applied full migration set on alias `tenant_3`; seeded owner staff `owner@lab.local` (random password, console-only); appended `127.0.0.1 lab.local.mountain.local` to `/etc/hosts` (fix applied for a newline glitch introduced during append; final file verified); added tenant CSRF origin to compose; rewrote DOCKER_SETUP.md tenant section; verified `POST /api/auth/login/` and `GET /api/auth/me/` through `http://lab.local.mountain.local:3000` → both 200; session + csrf cookies set.
- **Files changed**: `docker-compose.dev.yml` (CSRF origin), `docs/DOCKER_SETUP.md`; machine-level: `/etc/hosts`, central DB rows, new `lab_local` DB
- **Verification**: login 200 (`tenant_slug: lab`), me 200, HttpOnly sessionid + csrftoken cookies; inventory untouched
- **Change report**: `work/changes/2026-11-03--26-tenant-dev-setup-e2e-login.md`
- **PLAN / SUMMARY**: updated yes
