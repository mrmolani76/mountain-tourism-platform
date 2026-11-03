# Change Report: eQMS Complere module folder structure

- **Date**: 2026-11-03
- **By**: Agent
- **Change ID**: 71

## 1. Summary
Restructured `Modules/Saas` and `Modules/Core` to match `/var/www/html/eQMS/complere` Complere layout (`App/`, `routes/`, `config/`, `resources/`, `composer.json`, `module.json`, `vite`, `modules_statuses.json`).

## 2. Before
Flat module code: `Modules/Core/{Models,Http,Services,Providers,Enums,Routes}/` (same for Saas).

## 3. After (per module, eQMS Capas/Core shape)
```
Modules/{Name}/
  App/{Models,Http,Services,Providers,Enums,Policies,Data}/
  Database/{migrations[/tenant],Seeders,Factories}/
  routes/api.php (+ routes/api/)
  config/config.php
  resources/{views,assets}
  tests/{Feature,Unit}
  composer.json, module.json, package.json, vite.config.js
```
Namespaces: `Modules\{Name}\App\...`  
Providers: `Modules\{Name}\App\Providers\{Name}ServiceProvider` + `RouteServiceProvider`  
Root: `modules_statuses.json` (`Saas`, `Core` true)

## 4. Reason
User: same folder structure as EQMS Complere.

## 5. Files Affected
- Entire `backend-laravel/Modules/Core/**`, `Modules/Saas/**`
- `backend-laravel/modules_statuses.json`
- `backend-laravel/bootstrap/providers.php`
- `backend-laravel/routes/{api,tenant}.php`
- `backend-laravel/app/Models/*` aliases / middleware uses

## 6. Verification / Status
- [ ] Runtime not verified (no setup on this machine)
- [x] Tree mirrors eQMS Core/Capas/Saas layout
