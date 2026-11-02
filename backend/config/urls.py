"""
URL configuration — tenant APIs under /api/, central SaaS under /api/saas/.
"""

from django.contrib import admin
from django.urls import include, path
from rest_framework.schemas import get_schema_view

urlpatterns = [
    path('admin/', admin.site.urls),
    path('api/saas/', include('modules.saas.urls')),
    path('api/', include('modules.core.urls')),
    path('api/folio/', include('modules.folio.urls')),
    path('api/hotel/', include('modules.hotel.urls')),
    path('api/pos/', include('modules.pos.urls')),
    path('api/rental/', include('modules.rental.urls')),
    path('api/services/', include('modules.services.urls')),
    path('api/transport/', include('modules.transport.urls')),
    path('api/weather/', include('modules.weather.urls')),
    path('api/reports/', include('modules.reports.urls')),
    path('api/schema/', get_schema_view(
        title='Mountain Tourism API',
        description='Backend API for mountain tourism complex SaaS (MVP).',
        version='1.0.0',
    ), name='openapi-schema'),
]
