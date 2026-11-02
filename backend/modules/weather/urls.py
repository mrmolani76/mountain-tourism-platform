"""DRF routes for modules.weather (Module H — weather)."""

from django.urls import include, path
from rest_framework.routers import DefaultRouter

from modules.weather import views

router = DefaultRouter()
router.register(r'weather-limits', views.WeatherLimitViewSet, basename='weather-limit')
router.register(r'weather-warnings', views.WeatherWarningViewSet, basename='weather-warning')

urlpatterns = [
    path('', include(router.urls)),
]
