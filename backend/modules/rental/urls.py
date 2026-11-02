"""DRF routes for modules.rental (Module E — equipment rental)."""

from django.urls import include, path
from rest_framework.routers import DefaultRouter

from modules.rental import views

router = DefaultRouter()
router.register(r'rentals', views.RentalViewSet, basename='rental')

urlpatterns = [
    path('', include(router.urls)),
]
