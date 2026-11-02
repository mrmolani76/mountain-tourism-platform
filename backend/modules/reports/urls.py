"""DRF routes for modules.reports (Module I — dashboard and reports)."""

from django.urls import include, path
from rest_framework.routers import DefaultRouter

from modules.reports import views

router = DefaultRouter()
router.register(r'reports', views.ReportViewSet, basename='report')

urlpatterns = [
    path('', include(router.urls)),
]
