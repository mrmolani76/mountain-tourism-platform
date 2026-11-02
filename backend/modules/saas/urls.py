"""DRF router for central /api/saas/."""

from django.urls import include, path
from rest_framework.routers import DefaultRouter

from modules.saas import views

router = DefaultRouter()
router.register(r'tenants', views.TenantViewSet, basename='saas-tenant')

urlpatterns = [
    path('auth/login/', views.SaasLoginView.as_view(), name='saas-login'),
    path('auth/logout/', views.SaasLogoutView.as_view(), name='saas-logout'),
    path('auth/me/', views.SaasMeView.as_view(), name='saas-me'),
    path('', include(router.urls)),
]
