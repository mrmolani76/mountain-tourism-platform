---
id: BUG-APP-008
title: Staff detail URLs use numeric IDs instead of UUIDs
status: VERIFIED
type: APP
severity: medium
created: 2026-11-27
---

# BUG-APP-008: Staff detail URLs use numeric IDs instead of UUIDs

## 1. Summary
Staff view and edit links expose the predictable `core_staff` primary key in the URL instead of a stable UUID.

## 2. Environment
Local Mountain Tourism Laravel + Nuxt application; tenant `lab`.

## 3. Steps to Reproduce
1. Open the Staff listing.
2. Select View for a staff row.
3. Observe a numeric URL such as `/users/view/4`.

## 4. Expected
Staff view and edit URLs use the staff UUID, following Nexro's `/users/view/${item.uuid}` and `/users/form/${item.uuid}` pattern.

## 5. Actual
The listing and detail API serialize the numeric primary key as `uuid`; `core_staff` has no UUID column. The route and edit link therefore use values such as `4`.

## 6. Evidence
- User-provided screenshot shows `http://lab.local.mtmtp.local:3000/users/view/4`.
- `UserListingController::listRow()` assigns `(string) $s->getKey()` to `uuid` and builds the email URL from `$s->getKey()`.
- `UserListingController::detail()` and create/update responses also return the primary key as `uuid`.
- `2026_11_03_000001_create_core_staff_and_building_tables.php` creates `core_staff` without a UUID column.

## 7. Root Cause
Staff has no persisted UUID field; the backend aliases its auto-increment primary key as `uuid`, so frontend routes that correctly use `item.uuid` still expose the numeric ID.

## 8. Workaround
None.

## 9. Suggested Fix
Add a tenant migration that backfills and uniquely indexes real Staff UUIDs, generate UUIDs for new Staff records, and resolve Staff view/edit/reset endpoints using UUIDs while preserving internal numeric foreign keys.

## 9. Fix Progress
- Added a tenant migration to backfill and uniquely index Staff UUIDs.
- Updated Staff creation, auth payload, list/detail serialization, and resource/view/edit lookups to use UUIDs.
- Updated Nuxt route params and fetch requests to use UUIDs, following Nexro's listing pattern.
- Applied only the UUID migration to local `lab_local`; all 8 Staff records have unique non-null UUIDs.
- Browser verification passed for UUID links from the Staff listing and UUID-based detail/edit pages. No Staff data was edited.

## 10. Impact
Predictable internal Staff IDs appear in tenant Staff URLs. Existing server-side role checks still authorize Staff endpoints.

## 11. Related
- Work item: `work/inprogress/103-manual-browser-acceptance-checks.md`
- Change report: `work/changes/2026-11-27--103-staff-uuid-routes.md`
- Other bugs: none
