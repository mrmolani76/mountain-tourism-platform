---
title: Implement approved OSM maps for locations and routes
id: 112
priority: high
status: todo
milestone: 4
module: G-transport
area: frontend
effort: L
dependencies: 107
bug: none
created: 2026-11-26
updated: 2026-11-26
---

# Implement approved OSM maps for locations and routes

## What to do (What)
After provider approval, replace any unapproved Google Maps workflows and implement staff route geometry editing/rendering using the approved OSM-based map approach.

## Current / Before
Location selection uses Google Maps Places/Geocoder. Transport route creation accepts an optional first stop but does not edit or render the route geometry on a map.

## Expected / After
The approved free OSM-based map is used for location and transport map workflows, with OSM credit. Staff can create/edit route stops and path geometry without a routing service.

## Why
The MVP requires free OSM-based tiles, route paths drawn by staff, and OSM attribution. Use of other providers requires approval.

## Acceptance Criteria (complete only when all ticked)
- [ ] Confirm and follow the provider decision recorded in item 107.
- [ ] Replace unapproved Google Maps Places/Geocoder workflows.
- [ ] Add map UI to create/edit route stops and route geometry.
- [ ] Persist and reload route geometry through the existing transport API.
- [ ] Display required OSM attribution and test create/edit/read workflows.
- [ ] `work/changes/YYYY-MM-DD--112-<desc>.md` written.
- [ ] PLAN.md + SUMMARY.md updated.

## Files Likely Affected
- `frontend/components/dialogs/LocationSelectorDialog.vue`
- `frontend/plugins/google-map-loader.client.ts`
- `frontend/pages/transport/routes/index.vue`
- `frontend/composables/api-clients/useTransportApiClient.ts`
- `backend-laravel/Modules/Transport/`

## Notes / Follow-ups
- Do not add a package or external provider without approval.
- See [MVP scope audit](../../docs/MVP_SCOPE_AUDIT.md).

---

## Return to Pending Log (when work stops midway)

---

## Completion Log (when work is complete)

- **Completed by**:
- **Date**:
- **What was done**:
- **Files changed**:
- **Verification**:
- **Change report**:
- **PLAN / SUMMARY**:
