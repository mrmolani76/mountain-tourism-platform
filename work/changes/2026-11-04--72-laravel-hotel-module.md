# Change Report: Module 2 Hotel + Folio scaffolding via artisan

- **Date**: 2026-11-04
- **By**: Agent
- **Change ID**: 72

## 1. Summary

Created `Modules/Hotel` and `Modules/Folio` through `php artisan module:make` (not by hand),
pinned the nwidart config to the repo `App/` convention, and merged the Module 2 code into
the generated skeleton.

## 2. Before

- `backend-laravel/config/modules.php` did not exist, so nwidart used vendor defaults:
  `paths.app_folder = 'app/'` and generator paths under lowercase `app/...` and `database/...`.
- `Modules/Hotel` and `Modules/Folio` were hand-made folders with no `module.json`,
  no `composer.json`, no `config/config.php`, no `resources/`, no `tests/`.
- Nothing in `modules_statuses.json` for either module.
- Running `module:make` would have produced a module whose folder casing (`app/`) and
  namespace (`Modules\Hotel\...`) did not match the existing `Core` / `Saas` shape
  (`Modules\Core\App\...` in `App/`), which breaks on case-sensitive Linux deploys.

## 3. After

- `backend-laravel/config/modules.php` published (vendor config copy) with:
  - `paths.app_folder` = `App/`
  - every generator `path` under `App/...` (`App/Http/Controllers`, `App/Http/Requests`,
    `App/Models`, `App/Providers`, `App/Services`, `App/Console`, `App/Support` parents)
  - `factory` = `Database/Factories`, `migration` = `Database/migrations/tenant`,
    `seeder` = `Database/Seeders`
- `Modules/Hotel` and `Modules/Folio` regenerated with `php artisan module:make <Name> --api`:
  - generated `module.json`, `composer.json`, `package.json`, `vite.config.js`,
    `config/config.php`, `resources/`, `tests/`, `App/Providers/{Name}ServiceProvider.php`,
    `App/Providers/RouteServiceProvider.php`, `Database/Seeders` folder.
- Post-generation edits so generated files match `Core` / `Saas`:
  - `module.json` provider = `Modules\<Name>\App\Providers\<Name>ServiceProvider`
  - `composer.json` PSR-4 = `Modules\<Name>\App\` => `App/`, plus
    `Database\Factories` => `Database/Factories/`, `Database\Seeders` => `Database/Seeders/`
  - removed generated stub `App\Http\Controllers\HotelController.php`,
    `App\Providers\EventServiceProvider.php`, `routes/web.php`, `resources/views/index.blade.php`,
    `<Name>DatabaseSeeder.php` (routes and controllers are the real Module 2 ones)
- Module 2 code (models, services, controllers, requests, support, migration, routes)
  copied into the generated skeleton; `routes/api.php` keeps the hand-written route table.
- `bootstrap/providers.php` registers `FolioServiceProvider` then `HotelServiceProvider`.
- `modules_statuses.json` now lists `Hotel` and `Folio` as enabled.

## 4. Reason

User instruction: modules must be created with the artisan command, not hand-built folders.
Without the config change the generator emits lowercase `app/` folders and a
`Modules\Hotel\...` namespace, which would diverge from `Core`/`Saas` and break on Linux.

## 5. Files Affected

- `backend-laravel/config/modules.php` (new)
- `backend-laravel/modules_statuses.json`
- `backend-laravel/bootstrap/providers.php`
- `backend-laravel/Modules/Hotel/**` (regenerated skeleton + Module 2 code)
- `backend-laravel/Modules/Folio/**` (regenerated skeleton + folio slice)
- `backend-laravel/routes/console.php` (nightly posting schedule, unchanged in this step)

## 6. Verification / Status

- [x] `php artisan module:list` shows `Core`, `Saas`, `Hotel`, `Folio` enabled
- [x] `php artisan route:list` resolves all `api/hotel/*` and `api/folios/*` routes
      (controller classes load, no `ReflectionException`)
- [x] `php artisan list` shows `hotel:nightly-posting`
- [x] `composer dump-autoload` regenerates `Modules\<Name>\App\` PSR-4 mappings
- [ ] Tenant migrations not run (code-only task)
- [ ] `php artisan test` not run (code-only task)
