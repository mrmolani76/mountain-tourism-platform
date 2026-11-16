---
id: BUG-API-001
title: Core listing config shadowed — Unknown listing resource 404
status: VERIFIED
type: API
severity: high
created: 2026-11-16
---

# BUG-API-001: Core listing config shadowed — Unknown listing resource 404

## 1. Summary
`GET /api/buildings/list/config` returns 404 `Unknown listing resource` because app `config/listing.php` stub overwrites Core module buildings config (no `model`).

## 2. Environment
Local, branch main, after work 98.

## 3. Steps to Reproduce
1. Login as owner.
2. Open Core → Buildings.
3. Network: `GET /api/buildings/list/config` → 404.

## 4. Expected
200 with DataTable headers/`model`-backed config.

## 5. Actual
404 Envelope `not_found` / `Unknown listing resource.`

## 6. Evidence
`CoreListingController::resourceConfig` requires `model`. App stub `backend-laravel/config/listing.php` `buildings` has no `model`. `mergeConfigFrom` keeps app key over module.

## 7. Root Cause
Shallow `array_merge(module, app)` — app `buildings` wins without `model`/`headers`.

## 8. Workaround
None in UI.

## 9. Suggested Fix
Empty or remove app stub; harden controller fallback to `Modules/Core/config/listing.php`.

## 10. Impact
Core Buildings (and any shadowed resource) listing broken for owners.

## 11. Related
- Work item: 99
- Change report:
