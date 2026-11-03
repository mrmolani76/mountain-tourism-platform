# Change Report: Tenant dev setup — E2E staff login (item 26)

- **Date**: 2026-11-03
- **By**: OpenCode agent (user approved the setup on 2026-11-03; overrides code-only setup policy for this item)
- **Change ID**: 26

## 1. Summary
Made staff login work end-to-end on this machine: tenant `lab` (`lab.local.mountain.local`) registered, physical Postgres DB `lab_local` created, migrations applied on the tenant alias, owner staff user seeded, hosts entry added, CSRF origin added. Verified login and session via curl through the SPA nginx proxy.

## 2. Before
- SPA rendered (after item 25 / BUG-APP-001 fix) but `POST /api/auth/login/` via `127.0.0.1:3000` was rejected: `LoginView` requires a tenant host; `127.0.0.1` is central (`TENANCY_CENTRAL_HOSTS`)
- No tenant existed; no physical `lab_local` DB; no staff user; no hosts entry; CSRF origin for the tenant host missing

## 3. After
- `/etc/hosts`: `127.0.0.1 lab.local.mountain.local` (appended; existing lines untouched; a newline bug introduced during append was fixed immediately — final file verified)
- Central DB: tenant `lab` (id=3, db_name=`lab_local`), Domain `lab.local.mountain.local` (primary) registered via `provision_tenant`
- Postgres: `CREATE DATABASE lab_local OWNER mt_app`
- Tenant DB `lab_local`: full migration set applied on alias `tenant_3` (core, hotel, folio, pos, rental, services, transport, weather, reports, saas, sessions, admin/auth)
- Owner staff seeded: `owner@lab.local` (role owner, pk=1) with a random password printed once to console; not stored or committed anywhere
- `docker-compose.dev.yml`: `DJANGO_CSRF_TRUSTED_ORIGINS` now includes `http://lab.local.mountain.local:3000`; web container recreated, healthy
- `docs/DOCKER_SETUP.md`: tenant/login section rewritten with the real dev E2E steps
- Verified via curl through `http://lab.local.mountain.local:3000` (Host forwarded by SPA nginx):
  - `POST /api/auth/login/` → **200** `{"id":1,"email":"owner@lab.local","role":"Owner","role_code":"owner","is_active":true,"tenant_slug":"lab"}`
  - `GET /api/auth/me/` (cookie) → **200** same payload
  - `sessionid` (HttpOnly) and `csrftoken` cookies set for `lab.local.mountain.local`

## 4. Reason
User: "login wala kaam krke do" → "browser hit to hogya hai but kuch show nhi horha hai" → after BUG-APP-001 the login page rendered but staff login was impossible (central host). User explicitly approved full E2E setup on 2026-11-03.

## 5. Files Affected
- `docker-compose.dev.yml` (CSRF origin only)
- `docs/DOCKER_SETUP.md`
- `/etc/hosts` (machine-level, outside repo)
- Central Postgres DB (`Tenant`, `Domain` rows) and new tenant database `lab_local` (machine-level, outside repo)
- `work/inprogress/26-tenant-dev-setup-e2e-login.md`

## 6. Verification / Status
- [x] Tenant registered; `lab_local` created; migrations OK on `tenant_3`
- [x] Owner seeded; password not persisted anywhere in repo
- [x] `POST /api/auth/login/` → 200 via `http://lab.local.mountain.local:3000`
- [x] `GET /api/auth/me/` → 200 with tenant_slug=lab
- [x] Session cookie HttpOnly; CSRF cookie set
- [x] Inventory stack and hosts entries untouched (only appended `lab.local.mountain.local`)
- Notes: password printed once at creation; recreate the owner user instead of recovering it. `migrate_tenants` remains a dry-run stub by policy; migrations were applied explicitly via `call_command('migrate', database=alias)` in a one-off shell.

## 7. Usage
Open `http://lab.local.mountain.local:3000/` and log in with the dev owner credentials created on this machine. Steps to reproduce on another machine: `docs/DOCKER_SETUP.md` → "Tenant hosts and staff login (dev E2E)".
