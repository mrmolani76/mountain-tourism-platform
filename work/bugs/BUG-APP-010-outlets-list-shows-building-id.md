---
id: BUG-APP-010
title: Outlets listing shows building ID instead of name
status: RETESTING
type: APP
severity: medium
created: 2026-11-27
---

# BUG-APP-010: Outlets listing shows building ID instead of name

## 1. Summary
The Core Outlets listing displays the numeric `building_id` instead of the related Building name.

## 2. Environment
Local development, Nuxt frontend and Laravel Core module, item 126.

## 3. Steps to Reproduce
1. Open Core → Outlets.
2. View the Building column for an outlet.

## 4. Expected
Show the name of the Building linked to the outlet.

## 5. Actual
The Building column displays a numeric ID, for example `2`.

## 6. Evidence
- The user-provided screenshot shows `2` in the Building column.
- `Modules/Core/config/listing.php` configures the column with the `building_id` field.
- `CoreListingController::listRow()` converts the `building_id` value directly to a string.
- The listing now eager-loads the related Building and formats the existing `building_id` display cell with its name.
- A focused API regression test has been authored. The attempt to run it could not start because `php` is not available on the Windows host.

## 7. Root Cause
The Core listing serializer formats `building_id` as plain text and does not resolve the related Building name.

## 8. Workaround
Look up the Building by its ID in Core → Buildings.

## 9. Suggested Fix
Implemented in item 126: eager-load the tenant-scoped Building relation and return its name in the Building display cell. Keep the form and CRUD payload using `building_id`.

## 10. Impact
Staff and owners cannot identify an outlet's Building from the listing without looking up its numeric ID.

## 11. Related
- Work item: `work/todo/126-show-building-names-in-outlets.md`
- Change report: `work/changes/2026-11-27--126-outlet-building-name.md`
- Other bugs: None
