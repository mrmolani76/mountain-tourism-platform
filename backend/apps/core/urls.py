"""
URLs for apps.core.
"""
from django.urls import path
from . import views

app_name = 'core'

urlpatterns = [
    path('health/', views.health, name='health'),
    path('', views.api_root, name='api-root'),
    # Auth
    path('auth/login/', views.login, name='login'),
    path('auth/logout/', views.logout, name='logout'),
    path('auth/me/', views.me, name='me'),
    path('auth/reset-password/', views.reset_password, name='reset-password'),
    # Staff
    path('staff/', views.staff_list, name='staff-list'),
    path('staff/<int:pk>/', views.staff_detail, name='staff-detail'),
    # Staff <-> outlet access
    path('outlet-access/', views.outlet_access_list, name='outlet-access-list'),
    # Tenants (super admin surface in item 07)
    path('tenants/', views.tenant_list, name='tenant-list'),
]
