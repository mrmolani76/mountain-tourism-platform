# Change Report: Core module — Form Requests, audit wiring, foundation seed

- **Date**: 2026-11-03
- **By**: Agent
- **Change ID**: 71-core-parity

## 1. Summary
Hardened tenant `Modules/Core` toward Django Module A parity: typed Form Requests, 16 outlet types, AuditEvent writes on Core mutations + login, `auth/me` outlet list, and `core:seed-foundation`.

## 2. Before
- Only Building/Login had Form Requests; other Core controllers used inline `$request->validate()`
- `Outlet` accepted any `outlet_type` string
- `AuditService` existed with almost no write call sites
- No PDF §2 foundation seed command on Laravel
- `auth/me` lacked outlet access fields

## 3. After
- Form Requests for outlet/season/staff/outlet-access/settings/reset-password (+ shared `FailsWithEnvelope`)
- `Outlet::TYPES` = Django’s 16 choices; validated on store/update
- `CoreAudit` helper; wired on Building/Outlet/Season/Staff/OutletAccess/Settings + login/password reset
- `auth/me` returns `all_outlets` + `outlet_ids`
- `FoundationSeeder` + `php artisan core:seed-foundation` (optional `--reset`)

## 4. Reason
User: start missing Laravel work for **Core module only** (item 71 still in progress; verify AC still open).

## 5. Files Affected
- `Modules/Core/App/Http/Requests/**`
- `Modules/Core/App/Http/Controllers/{Auth,Building,Outlet,Season,Staff,OutletAccess,Settings}Controller.php`
- `Modules/Core/App/Models/Outlet.php`
- `Modules/Core/App/Support/CoreAudit.php`
- `Modules/Core/Database/Seeders/FoundationSeeder.php`
- `Modules/Core/App/Console/SeedFoundationCommand.php`
- `Modules/Core/App/Providers/CoreServiceProvider.php`
- `work/inprogress/71-laravel-modules-port.md`
- `work/changes/2026-11-03--71-core-form-requests-audit-seed.md`

## 6. Verification / Status
- [x] `php -l` on touched Core PHP files
- [ ] Feature/unit suite not run (code-only mode)
- [ ] Notes: Item 71 remains inprogress until setup-machine smoke + TENANT_ISOLATION_E2E
