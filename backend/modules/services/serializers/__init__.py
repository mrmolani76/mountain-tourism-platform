"""DRF serializers for modules.services (tenant DB)."""

from modules.services.serializers.service import ServiceSerializer
from modules.services.serializers.slot import SlotSerializer

__all__ = ['ServiceSerializer', 'SlotSerializer']
