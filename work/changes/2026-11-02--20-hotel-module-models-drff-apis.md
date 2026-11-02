# Change Report: hotel module — Room, Stay, BoardPlan models + DRF APIs

- **Date**: 2026-11-02
- **By**: Agent
- **Change ID**: 20

## 1. Summary
Implemented Module B (Hotel, chalets, huts) — BoardPlan, RoomType, Room, Stay models with DRF ViewSets, serializers, permissions, services, and registered URLs under /api/hotel/.

## 2. Before
`backend/modules/hotel/` was completely empty: empty `__init__.py`, empty `urls.py`, 0-line `views/__init__.py`, `serializers/__init__.py`, `models/__init__.py`. No hotel domain models or DRF APIs existed.

## 3. After
- **Models** (`backend/modules/otel/models/`): BoardPlan (room_only/bb/half_board/full_board), RoomType (capacity), Room (building+number unique), Stay (reserved/checked_in/checked_out/cancelled, board_plan, optional folio link).
- **Migrations** (`0001_initial.py`): Creates hotel_board_plan, hotel_room_type, hotel_room, hotel_stay tables; depends on core 0005 + folio 0001.
- **Serializers**: BoardPlanSerializer, RoomTypeSerializer, RoomSerializer, StaySerializer, StayCreateSerializer.
- **ViewSets**: BoardPlanViewSet, RoomTypeViewSet, RoomViewSet, StayViewSet (with check-in/check-out custom actions). Manager+ required for write.
- **Permissions**: IsHotelStaff (read), IsHotelManager (write).
- **Services**: check_in_stay, check_out_stay with status validation.
- **URLs**: Registered under /api/hotel/.

## 4. Reason
MVP scope Module B. Core domain: stays, rooms, board plans. Folio (Module C) depends on Stay for billing.

## 5. Files Affected
- `backend/modules/hotel/models/board_plan.py` (new)
- `backend/modules/hotel/models/room_type.py` (new)
- `backend/modules/hotel/models/room.py` (new)
- `backend/modules/hotel/models/stay.py` (new)
- `backend/modules/hotel/models/__init__.py` (new content)
- `backend/modules/hotel/migrations/0001_initial.py` (new)
- `backend/modules/hotel/serializers/board_plan.py` (new)
- `backend/modules/hotel/serializers/room_type.py` (new)
- `backend/modules/hotel/serializers/room.py` (new)
- `backend/modules/hotel/serializers/stay.py` (new)
- `backend/modules/hotel/serializers/__init__.py` (new content)
- `backend/modules/hotel/permissions/hotel.py` (new)
- `backend/modules/hotel/permissions/__init__.py` (new content)
- `backend/modules/hotel/services/stay.py` (new)
- `backend/modules/hotel/services/__init__.py` (new content)
- `backend/modules/hotel/views/hotel.py` (new)
- `backend/modules/hotel/views/__init__.py` (new content)
- `backend/modules/hotel/urls.py` (new content)
- `backend/config/urls.py` (added hotel URL include)

## 6. Verification / Status
- [ ] Not executed — code-only change (test DB setup pending per SUMMARY §3)
- [ ] Migration file created but not applied
- [ ] Change report written: this file
- [ ] PLAN.md + SUMMARY.md updated (item 20 moved Todo → Completed)
