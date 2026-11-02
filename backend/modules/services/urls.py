"""DRF routes for modules.services (Module F — hotel services and activities)."""

from django.urls import include, path
from rest_framework.routers import DefaultRouter

from modules.services import views

router = DefaultRouter()
router.register(r'services', views.ServiceViewSet, basename='service')
router.register(r'slots', views.SlotViewSet, basename='slot')

urlpatterns = [
    path('', include(router.urls)),
]
