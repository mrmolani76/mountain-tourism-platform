"""DRF routes for modules.transport (Module G — transport and lifts)."""

from django.urls import include, path
from rest_framework.routers import DefaultRouter

from modules.transport import views

router = DefaultRouter()
router.register(r'trips', views.TripViewSet, basename='trip')
router.register(r'vehicles', views.VehicleViewSet, basename='vehicle')
router.register(r'drivers', views.DriverViewSet, basename='driver')

urlpatterns = [
    path('', include(router.urls)),
]
