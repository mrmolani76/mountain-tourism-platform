"""DRF routes for modules.hotel (Module B — rooms, stays, board plans)."""

from django.urls import include, path
from rest_framework.routers import DefaultRouter

from modules.hotel import views

router = DefaultRouter()
router.register(r'board-plans', views.BoardPlanViewSet, basename='board-plan')
router.register(r'room-types', views.RoomTypeViewSet, basename='room-type')
router.register(r'rooms', views.RoomViewSet, basename='room')
router.register(r'stays', views.StayViewSet, basename='stay')

urlpatterns = [
    path('', include(router.urls)),
]
