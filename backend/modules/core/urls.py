"""DRF router + auth paths for tenant /api/."""

from django.urls import include, path
from rest_framework.routers import DefaultRouter

from modules.core import views

app_name = 'core'

router = DefaultRouter()
router.register(r'staff', views.StaffViewSet, basename='staff')
router.register(r'outlet-access', views.StaffOutletAccessViewSet, basename='outlet-access')

urlpatterns = [
    path('health/', views.HealthView.as_view(), name='health'),
    path('', views.ApiRootView.as_view(), name='api-root'),
    path('auth/login/', views.LoginView.as_view(), name='login'),
    path('auth/logout/', views.LogoutView.as_view(), name='logout'),
    path('auth/me/', views.MeView.as_view(), name='me'),
    path('auth/reset-password/', views.PasswordResetView.as_view(), name='reset-password'),
    path('', include(router.urls)),
]
