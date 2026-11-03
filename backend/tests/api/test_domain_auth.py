"""
Domain API authentication — every tenant domain ViewSet requires staff session auth.

Covers one representative list endpoint per domain module (hotel, folio, pos,
rental, services, transport, weather, reports):

- Unauthenticated requests → HTTP 401 (was 403 before the fix; empty
  authentication_classes disabled StaffSessionAuthentication).
- Authenticated tenant staff → reach permission checks (200 allowed, 403 denied).
- Tenant session binding continues to work (real login via session).
- Central SaaS authentication remains unaffected (staff session → 403 on /api/saas/).
"""

from django.contrib.auth.hashers import make_password
from django.test import TestCase, override_settings
from rest_framework.test import APIClient

from modules.core.models import Staff, StaffRole
from modules.saas.models import Domain, Tenant
from tenancy import connections, context

OTHER_TENANT_HOST = {'HTTP_HOST': 'other.test'}

# One representative list endpoint per domain module.
DOMAIN_ENDPOINTS = [
    ('hotel', '/api/hotel/rooms/'),
    ('folio', '/api/folio/folios/'),
    ('pos', '/api/pos/products/'),
    ('rental', '/api/rental/rentals/'),
    ('services', '/api/services/services/'),
    ('transport', '/api/transport/trips/'),
    ('weather', '/api/weather/weather-limits/'),
    ('reports', '/api/reports/reports/'),
]


@override_settings(
    TENANCY_CENTRAL_HOSTS=('saas.test',),
    TENANCY_CENTRAL_PREFIX='saas.',
    ALLOWED_HOSTS=('lab.test', 'other.test', 'saas.test', 'testserver', 'localhost', '127.0.0.1'),
)
class DomainAuthTests(TestCase):
    databases = {'default', 'tenant_1', 'tenant_2'}

    def setUp(self):
        self.client = APIClient()
        self.tenant = Tenant.objects.create(
            id=1, name='Test Complex', slug='lab', db_name='lab_test', is_active=True
        )
        Domain.objects.create(tenant=self.tenant, hostname='lab.test', is_primary=True)
        self.alias = connections.ensure_tenant_connection(self.tenant)
        context.set_current_tenant(self.tenant, alias=self.alias)
        try:
            self.owner = Staff.objects.create(
                email='owner@test.com', password=make_password('ownerpass'),
                role=StaffRole.OWNER, is_active=True,
            )
            self.manager = Staff.objects.create(
                email='manager@test.com', password=make_password('managerpass'),
                role=StaffRole.MANAGER, is_active=True,
            )
            self.staff = Staff.objects.create(
                email='staff@test.com', password=make_password('staffpass'),
                role=StaffRole.FRONT_DESK, is_active=True,
            )
        finally:
            context.clear_current_tenant()

        self.tenant_host = {'HTTP_HOST': 'lab.test'}

    # --- unauthenticated → 401 on every domain list endpoint ----------------

    def test_unauthenticated_domain_endpoints_return_401(self):
        for name, url in DOMAIN_ENDPOINTS:
            with self.subTest(module=name):
                response = self.client.get(url, **self.tenant_host)
                self.assertEqual(
                    response.status_code, 401,
                    f'{name} {url} should return 401 unauthenticated, got {response.status_code}'
                )

    # --- authenticated owner → 200 on every domain list endpoint -----------

    def test_authenticated_owner_can_read_domain_endpoints(self):
        self.client.force_authenticate(user=self.owner)
        for name, url in DOMAIN_ENDPOINTS:
            with self.subTest(module=name):
                response = self.client.get(url, **self.tenant_host)
                self.assertEqual(
                    response.status_code, 200,
                    f'{name} {url} should return 200 for owner, got {response.status_code}'
                )

    # --- authenticated front desk → 200 on list (IsTenantStaff allows) -----

    def test_authenticated_staff_can_read_domain_endpoints(self):
        self.client.force_authenticate(user=self.staff)
        for name, url in DOMAIN_ENDPOINTS:
            with self.subTest(module=name):
                response = self.client.get(url, **self.tenant_host)
                self.assertEqual(
                    response.status_code, 200,
                    f'{name} {url} should return 200 for front desk staff, got {response.status_code}'
                )

    # --- manager-only write denied for front desk → 403 ---------------------

    def test_front_desk_denied_manager_write_on_domain(self):
        self.client.force_authenticate(user=self.staff)
        # Room create requires IsHotelManager; front desk is not a manager.
        # Permission runs before serializer validation, so an invalid payload
        # still yields the permission decision.
        response = self.client.post(
            '/api/hotel/rooms/', {'name': 'X'}, format='json', **self.tenant_host
        )
        self.assertEqual(response.status_code, 403)

    # --- session binding via real login -------------------------------------

    def test_login_session_binds_to_domain_endpoint(self):
        self.client.post(
            '/api/auth/login/',
            {'email': self.owner.email, 'password': 'ownerpass'},
            format='json', **self.tenant_host,
        )
        self.assertEqual(self.client.session.get('staff_tenant_id'), self.tenant.pk)
        response = self.client.get('/api/hotel/rooms/', **self.tenant_host)
        self.assertEqual(response.status_code, 200)

    # --- central SaaS auth unaffected --------------------------------------

    def test_saas_endpoint_unauthenticated_still_denied(self):
        # SaaS auth is untouched by this change: unauthenticated still denied (403).
        response = self.client.get('/api/saas/auth/me/', **{'HTTP_HOST': 'saas.test'})
        self.assertEqual(response.status_code, 403)

    def test_staff_session_rejected_by_saas_endpoint(self):
        self.client.post(
            '/api/auth/login/',
            {'email': self.owner.email, 'password': 'ownerpass'},
            format='json', **self.tenant_host,
        )
        response = self.client.get('/api/saas/auth/me/', **{'HTTP_HOST': 'saas.test'})
        self.assertEqual(response.status_code, 403)
