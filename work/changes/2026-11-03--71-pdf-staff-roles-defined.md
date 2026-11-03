# Change Report: PDF §3 roles defined (StaffRole + SaasRole)

- **Date**: 2026-11-03
- **By**: Agent
- **Change ID**: 71

## 1. Summary
Defined Mountain MVP Scope v1 §3 roles in Laravel Module 1: tenant `StaffRole` enum + central `SaasRole::SUPER_ADMIN`; Spatie role seeder stub; staff API validates role codes.

## 2. Before
Roles lived only as constants on `Modules\Core\Models\Staff`. No dedicated enum, no Saas role helper, staff create/update accepted any string role.

## 3. After
| PDF role | Code | Where |
|----------|------|-------|
| Super admin | `super_admin` | `Modules\Saas\Enums\SaasRole` / `SaasUser` (central) |
| Owner | `owner` | `Modules\Core\Enums\StaffRole` (tenant) |
| Manager | `manager` | same |
| Front desk | `front_desk` | same |
| Cashier | `cashier` | same |
| Rental desk | `rental_desk` | same |
| Service staff | `service_staff` | same |
| Transport desk | `transport_desk` | same |
| Lift operator | `lift_operator` | same |
| Drivers | — | no login (PDF) |

Also: `StaffRoleSeeder` for Spatie `Role::findOrCreate` (run on setup machine later).

## 4. Reason
User: define roles according to the PDF.

## 5. Files Affected
- `backend-laravel/Modules/Core/Enums/StaffRole.php`
- `backend-laravel/Modules/Core/Models/Staff.php`
- `backend-laravel/Modules/Core/Http/Controllers/StaffController.php`
- `backend-laravel/Modules/Core/Database/Seeders/StaffRoleSeeder.php`
- `backend-laravel/Modules/Saas/Enums/SaasRole.php`
- `backend-laravel/Modules/Saas/Models/SaasUser.php`

## 6. Verification / Status
- [ ] Spatie tables / seeder not run (no setup on this machine)
- [x] Codes match Django `StaffRole` + PDF §3
