"""Hotel models (Module B)."""

from modules.hotel.models.board_plan import BoardPlan, BoardPlanKind
from modules.hotel.models.room_type import RoomType
from modules.hotel.models.room import Room
from modules.hotel.models.stay import Stay, StayStatus

__all__ = [
    'BoardPlan',
    'BoardPlanKind',
    'RoomType',
    'Room',
    'Stay',
    'StayStatus',
]
