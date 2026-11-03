# Change Report: EQMS Spatie/API + audit packages declared

- **Date**: 2026-11-03
- **By**: Agent
- **Change ID**: 71

## 1. Summary
Checked `/var/www/html/eQMS/complere` composer; declared matching API Spatie + audit package (owen-it) for Mountain Laravel.

## 2. Before
`composer.json` had `spatie/laravel-data` + `spatie/laravel-permission` only (no model audit package).

## 3. After
| Source (eQMS) | Package | Mountain declare |
|---------------|---------|------------------|
| API DTOs | `spatie/laravel-data` ^4.5 | already `^4.17` |
| Roles | `spatie/laravel-permission` ^6.4 | already `^6.21` |
| Model audit trail | `owen-it/laravel-auditing` ^13.6 | added `^14.0` (Inventory pin; Laravel 13) |
| PDF text extract | `spatie/pdf-to-text` | **skipped** (not Module 1 API) |
| Dev | `spatie/laravel-ignition` | **skipped** (dev UX only) |

Note: eQMS audit is **owen-it**, not Spatie Activitylog. Domain money audit (`AuditEvent`) stays custom append-only; owen-it covers Eloquent model change trails like Complere/Inventory.

## 4. Reason
User: check EQMS for Spatie packages used for audit + API and add those.

## 5. Files Affected
- `backend-laravel/composer.json`

## 6. Verification / Status
- [ ] Not installed on this machine
- [ ] Config publish / audits migrations = setup machine later
