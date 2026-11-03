# Change Report: Module 1 Laravel — Inventory central SaaS + tenant Core

- **Date**: 2026-11-03
- **By**: Agent
- **Change ID**: 71

## 1. Summary
Scoped Laravel port to PDF Module A only: central `Modules/Saas` + tenant `Modules/Core`, Inventory-style route split; removed B–I module stubs and kanban 35–66.

## 2. Before
- `backend-laravel/Modules/` had Saas, Core, Hotel, Folio, Pos, Rental, Services, Transport, Weather, Reports
- Pilot auth/buildings still registered in `routes/api.php` via `App\Http\Controllers\Api\*`
- Domain lookup used column `domain`; Building fillable used `map_lat`/`map_lng`; Season had `name`; AuditEvent used non-DRF field names
- Work PLAN Todo listed items 35–66; item 71 scoped A–I full port

## 3. After
- Only `Modules/Saas` (central) and `Modules/Core` (tenant) remain
- `routes/api.php` = health + Saas routes; `routes/tenant.php` = Core routes via `CoreServiceProvider`
- Providers registered in `bootstrap/providers.php`; `saas.auth` middleware alias added
- DRF parity: `hostname`, `latitude`/`longitude`, Season without name, AuditEvent `action`/`entity_*`
- Migrations: central `saas_user`; tenant outlet/season/settings/outlet_access/audit
- PLAN/SUMMARY/item 71 narrowed to Module 1; todos 35–66 removed from `work/todo/`

## 4. Reason
User: keep Module 1, remove the rest; check InventorySystem-docker + MVP PDF — one central = SaaS, remaining = modules.

## 5. Files Affected
- `backend-laravel/Modules/{Hotel,Folio,Pos,Rental,Services,Transport,Weather,Reports}/` (deleted)
- `backend-laravel/routes/api.php`, `routes/tenant.php`
- `backend-laravel/bootstrap/{app,providers}.php`
- `backend-laravel/Modules/Saas/**`, `backend-laravel/Modules/Core/**`
- `backend-laravel/app/Tenancy/TenantResolver.php`
- `backend-laravel/database/migrations/2026_11_03_000002_create_saas_user_table.php`
- `backend-laravel/database/migrations/tenant/2026_11_03_000002_create_core_config_tables.php`
- `work/todo/35-*.md` … `66-*.md` (deleted)
- `work/inprogress/71-laravel-modules-port.md`, `work/PLAN.md`, `work/SUMMARY.md`
- `work/todo/README-all-modules-todos.md`

## 6. Verification / Status
- [ ] Tested / Verified? No — code-only; this host has no Docker/`composer` run for Inventory or Laravel
- [x] Migrations/Scripts pending on setup machine (`migrate` central + tenant path)
- [x] Inventory layout inspected from disk at `../InventorySystem-docker` (docker CLI not installed here)
- [x] MVP PDF Module A checked via `pdftotext` on `document/Mountain_MVP_Scope_v1.pdf`
