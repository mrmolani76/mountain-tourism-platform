---
title: hotel module — Room, Stay, BoardPlan models + DRF APIs
id: 20
priority: high
status: completed
milestone: 2
module: B-hotel
area: backend
effort: L
dependencies: 18
bug: none
created: 2026-11-02
updated: 2026-11-02
---

# hotel module — Room, Stay, BoardPlan models + DRF APIs

## What to do
Implement Module B (Hotel, chalets, huts) in `backend/modules/hotel/`:
- Models: RoomType, Room, Stay, BoardPlan, Reservation
- Serializers for all models
- ViewSets: RoomViewSet, StayViewSet, BoardPlanViewSet
- Permissions: role-based (front desk / cashier / owner)
- URLs: register hotel routes in config/urls.py
- Services: check_in, check_out, assign_room

## Current / Before
`backend/modules/hotel/` has empty `__init__.py`, `urls.py` (empty urlpatterns), `views/__init__.py` (0 lines), `serializers/__init__.py` (0 lines), `models/__init__.py` (0 lines). No models, views, serializers, or DRF routes exist.

## Expected / After
hotel module has Room/Stay/BoardPlan models, DRF ViewSets with permissions, serializers, and registered URLs under /api/hotel/.

## Why
MVP scope Module B. Core domain: stays, rooms, board plans. Folio depends on Stay.

## Acceptance Criteria
- [ ] RoomType, Room, Stay, BoardPlan models created with migrations
- [ ] DRF ViewSets + serializers + permissions
- [ ] URLs registered in config/urls.py
- [ ] Change report written
- [ ] PLAN.md + SUMMARY.md updated

## Files Likely Affected
- `backend/modules/hotel/models/`
- `backend/modules/hotel/serializers/`
- `backend/modules/hotel/views/`
- `backend/modules/hotel/urls.py`
- `backend/modules/hotel/services/`
- `backend/modules/hotel/permissions/`
- `backend/modules/hotel/migrations/`
- `backend/config/urls.py`

## Notes / Follow-ups
None.

## Return to Pending Log
None.

## Completion Log
- **Completed by**: Agent
- **Date**: 2026-11-02
- **What was done**: Implemented Module B (hotel) — BoardPlan, RoomType, Room, Stay models with DRF ViewSets, serializers, permissions, services, and registered URLs under /api/hotel/. Stay has check-in/check-out custom actions. Room has unique_together on building+number.
- **Files changed**: backend/modules/hotel/models/{board_plan,room_type,room,stay}.py, backend/modules/hotel/models/__init__.py, backend/modules/hotel/migrations/0001_initial.py, backend/modules/hotel/serializers/{board_plan,room_type,room,stay}.py, backend/modules/hotel/serializers/__init__.py, backend/modules/hotel/permissions/hotel.py, backend/modules/hotel/permissions/__init__.py, backend/modules/hotel/services/stay.py, backend/modules/hotel/services/__init__.py, backend/modules/hotel/views/hotel.py, backend/modules/hotel/views/__init__.py, backend/modules/hotel/urls.py, backend/config/urls.py
- **Verification**: Code-only change, not executed (test DB setup pending per SUMMARY §3)
- **Change report**: work/changes/2026-11-02--20-hotel-module-models-drff-apis.md
- **PLAN / SUMMARY**: updated yes