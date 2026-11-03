"""DRF serializers for modules.hotel (tenant DB)."""

from modules.hotel.serializers.board_plan import BoardPlanSerializer
from modules.hotel.serializers.room import RoomSerializer
from modules.hotel.serializers.room_type import RoomTypeSerializer
from modules.hotel.serializers.stay import StayCreateSerializer, StaySerializer

__all__ = [
    'BoardPlanSerializer',
    'RoomTypeSerializer',
    'RoomSerializer',
    'StaySerializer',
    'StayCreateSerializer',
]
