# Change Report: Module 1 PHPUnit suite (Core + Saas)

- **Date**: 2026-11-03
- **By**: Agent
- **Change ID**: 71-phpunit-modules

## 1. Summary
Added Laravel PHPUnit coverage for Module 1 (Core + Saas), with a SQLite dual-connection `TenancyTestCase` harness. Tests authored only; not executed (testing-agent default).

## 2. Before
- Example tests + thin StaffRole / isolation contract stubs

## 3. After
- `tests/Support/TenancyTestCase.php` — central + tenant SQLite fixtures
- Feature: Core auth, buildings/outlets/seasons/settings/staff, isolation; Saas login/registry/activate
- Unit: AuditEvent append-only, Staff outlet access helpers
- `phpunit.xml` Modules testsuite

## 4. Reason
User asked for PHPUnit tests for the module(s).

## 5. Files Affected
- `tests/Support/TenancyTestCase.php`
- `tests/Feature/Modules/Core/{AuthApiTest,BuildingOutletSeasonTest,TenantIsolationTest}.php`
- `tests/Feature/Modules/Saas/SaasRegistryTest.php`
- `tests/Unit/Modules/Core/{AuditEventTest,StaffOutletAccessTest}.php`
- `phpunit.xml`
- `work/changes/2026-11-03--71-module1-phpunit-tests.md`

## 6. Verification / Status
- [ ] Tested / Verified? No — do not run on this agent host; setup machine: `php artisan test --testsuite=Modules`
- [ ] Notes: Fixture password is the documented seed value `password123` (same as DevPilotSeeder).
