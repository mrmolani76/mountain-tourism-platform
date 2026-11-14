---
title: Local MySQL switch for browser testing
id: 90
priority: high
status: todo
milestone: none
module: infra
area: devops
effort: S
dependencies: none
bug: none
created: 2026-11-14
updated: 2026-11-14
---

# Local MySQL for browser testing

## What to do (What)

Switch local Laravel from PostgreSQL to MySQL so Apache + Nuxt browser testing can run (pdo_pgsql missing; MySQL available). Use EQMS-style hosts and DB-per-tenant (not shared DB).

## Current / Before

- `backend-laravel/.env`: typically `DB_CONNECTION=pgsql`, port `5432`
- `config/database.php` `tenant` connection hardcoded `pgsql`
- `tenants:provision-local` uses Postgres-only SQL (`pg_database`, `pg_terminate_backend`)
- Target host pattern: `{slug}.local.mountain.local` / `saas.local.mountain.local`

## Expected / After

- Local `.env` uses MySQL (`DB_CONNECTION=mysql`, port `3306`)
- Tenant connection follows central driver
- `tenants:provision-local` works on MySQL
- Bootstrap script creates central + tenant DBs + app user
- Lab login works at `http://lab.local.mountain.local/login` (`owner@lab.test` / `password123`)
- Host pattern EQMS-style DB-per-tenant: `{slug}.local.mountain.local` (not shared DB)

## Why

Browser testing blocked on missing Postgres driver on some machines; user asked MySQL + EQMS hosts.

## Acceptance Criteria (complete only when all ticked)

- [ ] Local `.env` on MySQL; `php artisan db:show` succeeds
- [ ] Central migrate + `tenants:provision-local lab` succeed
- [ ] Browser can open login on `lab.local.mountain.local`
- [ ] `work/changes/YYYY-MM-DD--90-*.md` written
- [ ] PLAN.md + SUMMARY.md updated

## Files Likely Affected

- `backend-laravel/.env` (local only)
- `backend-laravel/config/database.php`
- `backend-laravel/app/Console/Commands/ProvisionLocalTenantCommand.php`
- `scripts/mysql-local-bootstrap.sh` (new)
- `docs/apache/mtmtp-lab.local.mountain.local.conf` (new)
- `docs/apache/mtmtp-saas.local.mountain.local.conf` (new)

## Notes / Follow-ups

- Do not change product default away from Postgres in `.env.example` unless asked.
- Prefer EQMS hosts over plain `localhost`.
- 2026-11-14: partial attempt on this machine reverted; finish on another machine.

## Return to Pending Log

- **Returned by**: Agent
- **Date**: 2026-11-14
- **Reason**: User asked to stop setup and revert; will complete on another machine.
- **Next step**: On target machine: MySQL bootstrap, tenant-driver config, provision `lab` with `{slug}.local.mountain.local`, Apache + hosts, then browser login verify.
