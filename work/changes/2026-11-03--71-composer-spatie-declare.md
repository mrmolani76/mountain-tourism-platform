# Change Report: Declare Spatie packages in composer.json

- **Date**: 2026-11-03
- **By**: Agent
- **Change ID**: 71

## 1. Summary
Declared Inventory-aligned Spatie packages in `backend-laravel/composer.json` (no install on this machine).

## 2. Before
`require` had framework, sanctum, tinker, stancl/tenancy, nwidart/laravel-modules only.

## 3. After
Added:
- `spatie/laravel-data` `^4.17`
- `spatie/laravel-permission` `^6.21`

Skipped `spatie/laravel-one-time-passwords` (MVP PDF: no email sending).

## 4. Reason
User asked to add Spatie to composer; Inventory uses these for DTOs + roles.

## 5. Files Affected
- `backend-laravel/composer.json`

## 6. Verification / Status
- [ ] Not installed here (`composer install` = setup machine only)
- [ ] Package publish / permission tables = setup machine later
