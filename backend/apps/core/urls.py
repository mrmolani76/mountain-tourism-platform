"""
URLs for apps.core.
"""
from django.urls import path
from . import views

app_name = 'core'

urlpatterns = [
    path('health/', views.health, name='health'),
    path('', views.api_root, name='api-root'),
    path('tenants/', views.tenant_list, name='tenant-list'),
    path('staff/', views.staff_list, name='staff-list'),
    path('staff/<int:pk>/', views.staff_detail, name='staff-detail'),
]
