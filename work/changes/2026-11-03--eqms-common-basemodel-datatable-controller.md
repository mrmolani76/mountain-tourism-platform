# Change Report: eQMS common BaseModel / DataTable / Controller

- **Date**: 2026-11-03
- **By**: Agent
- **Change ID**: 71-eqms-commons

## 1. Summary
Added Complere shared platform pieces into `backend-laravel`: BaseModel, BasePivot, BaseAuditTrail, CustomAuditable, DataTableService, DateTimeZoneMutator, base Controller (`handleAction`), helpers, listing + audit config; wired Core/Saas models and controllers.

## 2. Before
- Empty `app/Http/Controllers/Controller.php`
- Module models extended raw `Model`
- Module controllers were plain classes (no shared handleAction)
- No DataTable / listing config layer

## 3. After
- Commons under `app/Models`, `app/Services`, `app/Traits`, `app/Helpers`
- `config/listing.php`, `config/audit.php` (auditing off by default)
- Models extend `BaseModel`; controllers extend `Controller`
- Docs: `backend-laravel/docs/EQMS_COMMON_PLATFORM.md`

## 4. Reason
User asked to add eQMS Complere common add-ons (DataTable, BaseModel, Controller) into Mountain Laravel.

## 5. Files Affected
- `app/Models/{BaseModel,BasePivot,BaseAuditTrail}.php`
- `app/Traits/CustomAuditable.php`
- `app/Services/{DataTableService,DateTimeZoneMutator}.php`
- `app/Http/Controllers/Controller.php`
- `app/Helpers/helpers.php`
- `config/{listing,audit}.php`
- `composer.json` (files autoload)
- `Modules/Core/App/Models/*`, `Modules/Saas/App/Models/{Tenant,Domain}.php`
- `Modules/*/App/Http/Controllers/*.php`
- `backend-laravel/docs/EQMS_COMMON_PLATFORM.md`
- `work/changes/2026-11-03--eqms-common-basemodel-datatable-controller.md`

## 6. Verification / Status
- [ ] Tested / Verified? No — code-only; `composer dump-autoload` on setup machine.
- [ ] Migrations/Scripts pending? No new schema; `created_by` columns optional later.
- [ ] Notes: owen-it auditing disabled until hist tables exist; money audit remains `core_audit_event`.
