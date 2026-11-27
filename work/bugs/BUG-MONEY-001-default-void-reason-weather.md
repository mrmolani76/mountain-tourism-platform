---
id: BUG-MONEY-001
title: Generic folio void defaults to weather reason
status: CLOSED
type: MONEY
severity: medium
created: 2026-11-26
---

# BUG-MONEY-001: Generic folio void defaults to weather reason

## 1. Summary
If a folio void request omits `reason_code`, the service records `weather` even for a normal staff void. This can misclassify voids in audit and weather-cancellation reports.

## 2. Environment
Verified in the local Laravel test environment using PHP 8.4.28 and SQLite.

## 3. Steps to Reproduce
1. Submit a valid folio void request with `charge_id` or `payment_id` and a `reason`, but omit `reason_code`.
2. Inspect the resulting `folio_void.reason_code`.

## 4. Expected
The API should require an explicit reason code or use a non-weather default for ordinary voids. Weather cancellation should record `weather` only when explicitly requested by that workflow.

## 5. Actual
`VoidRequest` marks `reason_code` as optional, while `FolioService::voidEntry()` defaults an omitted value to `FolioVoid::REASON_WEATHER`.

## 6. Evidence
- [VoidRequest.php](../../backend-laravel/Modules/Folio/App/Http/Requests/VoidRequest.php)
- [FolioService.php](../../backend-laravel/Modules/Folio/App/Services/FolioService.php)
- Focused JUnit report: 2 tests, 8 assertions, 0 failures/errors. Coverage verifies the default `other` reason and explicit `weather`.
- Running the full `HotelModuleTest` file also confirms a separate validation error (`Exists::connection()`), tracked as BUG-API-002.

## 7. Root Cause
The API boundary allows `reason_code` to be absent, and the service fallback is the weather-specific reason.

## 8. Workaround
Clients can send an explicit non-weather reason code for ordinary voids. This does not change the API default.

## 9. Suggested Fix
Choose a domain-safe ordinary-void default or require a reason code, and keep the weather cancellation path explicitly set to `weather`. Add a focused API/service regression test.

## 10. Impact
Folio void audit classification and weather-cancellation reporting for void requests that omit `reason_code`.

## 11. Related
- Work item: 106
- Change report: `work/changes/2026-11-26--105-mvp-scope-project-audit.md`
- Other bugs: none

## 12. Current Work
- **2026-11-27**: Service fallback changed to `other`; regression assertions cover omitted `reason_code` and explicit `weather`.
- **Status**: CLOSED after focused verification. The PHP test invocation returned exit code 1 despite the clean focused JUnit report; the broader Hotel test file has 8 failures from BUG-API-002, tracked separately.
