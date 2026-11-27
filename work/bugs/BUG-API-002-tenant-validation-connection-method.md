---
id: BUG-API-002
title: Tenant validation calls an unsupported connection method
status: VERIFIED
type: API
severity: high
created: 2026-11-27
---

# BUG-API-002: Tenant validation calls an unsupported connection method

## 1. Summary
Tenant-scoped validation helpers call `connection()` on Laravel's `Exists` validation rule. The installed Laravel 13.39 rule does not provide that method, so affected requests throw an error and return HTTP 500 instead of validating input.

## 2. Environment
Local Laravel test environment using the locked Laravel 13.39.0 dependency and PHP 8.4.28.

## 3. Steps to Reproduce
1. Submit a hotel reservation request that validates a tenant-owned reference, such as `board_plan_id`.
2. Or submit a folio charge request that reaches `StoreChargeRequest`.
3. Observe the validation request fail before returning a validation response.

## 4. Expected
Validation rules query the active tenant database and return a normal success or validation response.

## 5. Actual
The requests fail with `Call to undefined method Illuminate\Validation\Rules\Exists::connection()` and HTTP 500.

## 6. Evidence
- Tenant `exists` helpers in Hotel, Folio, Pos, Rental, Services, Transport, Weather, and Reports now use Laravel's `connection.table` syntax; Hotel `unique` rules use the same supported syntax.
- Active-tenant isolation, room-season-price exists/unique, weather-cancellation, charge-to-room, and weather-void API tests passed: 5 tests, 17 assertions.
- The full Hotel feature test file had no validation HTTP 500s; two unrelated SQLite back-to-back reservation assertions returned 409 and are documented in the item 120 report.

## 7. Root Cause
The validation rule builder API does not include `Exists::connection()`, but shared module helpers call it while creating tenant-scoped rules.

## 8. Workaround
No production workaround recorded. The regression test seeds the Folio and Charge rows directly so it can isolate the void endpoint while this separate validation issue is tracked.

## 9. Suggested Fix
Use Laravel-supported tenant-qualified table syntax for `exists` and `unique` validation rules. Add focused validation coverage proving queries use the active tenant connection.

## 10. Impact
Tenant-backed request validation in multiple business modules can throw HTTP 500 before business logic executes.

## 11. Related
- Work item: 120
- Change report: [2026-11-27--120-tenant-validation-rules.md](../changes/2026-11-27--120-tenant-validation-rules.md)
- Discovered while working on item 106 (BUG-MONEY-001)
- Other bugs: none
