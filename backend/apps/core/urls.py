"""
URLs for apps.core.
"""
from django.urls import path
from . import views

app_name = 'core'

urlpatterns = [
    path('health/', views.health, name='health'),
    path('', views.api_root, name='api-root'),
]
