# Change Report: Work 106 — Folio Void Reason Default

- **Date**: 2026-11-27
- **By**: Copilot
- **Change ID**: 106-folio-void-reason

## 1. Summary
Changed omitted generic folio void reason codes to `other` and added regression assertions while preserving explicit weather voids.

## 2. Before
- `FolioService::voidEntry()` used `FolioVoid::REASON_WEATHER` when callers omitted `reason_code`.
- The existing manager-void feature test checked append-only charge behavior but not the returned void reason code.

## 3. After
- Added `FolioVoid::REASON_OTHER` and used it as the service fallback.
- Extended the generic void feature test to assert the `other` code.
- Added a feature test asserting an explicit `weather` reason code is preserved.
- Corrected `HotelModuleTest` to load current split module migrations and isolate void tests from unrelated Hotel/Folio `exists` validation errors.
- Installed PHP 8.4.28 user-scope and configured the CLI extensions needed by the locked dependencies and SQLite tests.

## 4. Reason
An ordinary staff void must not be misclassified as a weather cancellation in audit history or reports.

## 5. Files Affected
- `backend-laravel/Modules/Folio/App/Models/FolioVoid.php`
- `backend-laravel/Modules/Folio/App/Services/FolioService.php`
- `backend-laravel/tests/Feature/Modules/Hotel/HotelModuleTest.php`
- PHP 8.4.28 user-level CLI configuration (outside repository)
- `work/completed/106-fix-generic-folio-void-reason.md`
- `work/bugs/BUG-MONEY-001-default-void-reason-weather.md`
- `work/bugs/INDEX.md`
- `work/PLAN.md`
- `work/SUMMARY.md`
- `work/MEMORY.md`
- `work/changes/2026-11-27--106-folio-void-reason.md`

## 6. Verification / Status
- [x] Confirmed database schema default is `other`, and the weather cancellation service explicitly passes `FolioVoid::REASON_WEATHER`.
- [x] Added regression assertions for ordinary void default and explicit weather code.
- [x] Installed PHP 8.4.28 with cURL, fileinfo, mbstring, OpenSSL, PDO SQLite, and SQLite extensions.
- [x] Focused JUnit report: 2 tests, 8 assertions, 0 failures/errors. The test invocation still returned process exit code 1.
- [x] Full `HotelModuleTest` report: 3 passed, 8 failed. Each failure is an HTTP 500 from the independently tracked `Exists::connection()` bug (BUG-API-002 / TODO 120), not the folio void behavior.
- [x] Item 106 completed; BUG-MONEY-001 closed. BUG-API-002 remains open and is not part of this fix.
