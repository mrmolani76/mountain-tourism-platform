"""DRF routes for modules.folio (Module C — folio, charges, payments)."""

from django.urls import include, path
from rest_framework.routers import DefaultRouter

from modules.folio import views

router = DefaultRouter()
router.register(r'folios', views.FolioViewSet, basename='folio')
router.register(r'charges', views.ChargeViewSet, basename='charge')
router.register(r'payments', views.PaymentViewSet, basename='payment')

urlpatterns = [
    path('', include(router.urls)),
]
