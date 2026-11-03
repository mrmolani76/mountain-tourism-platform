"""
DB-per-tenant isolation: staff in tenant A DB must not appear when B is active.

Code only — requires tenant DB aliases migrated when tests are later executed.
"""


from django.contrib.auth.hashers import make_password
from django.test import TestCase, override_settings
from rest_framework.test import APIClient, APIRequestFactory, force_authenticate

from modules.core.models import Staff, StaffRole
from modules.core.views import StaffViewSet
from modules.saas.models import Domain, Tenant
from tenancy import connections, context


@override_settings(
    TENANCY_CENTRAL_HOSTS=('saas.test',),
    TENANCY_CENTRAL_PREFIX='saas.',
    ALLOWED_HOSTS=('lab.test', 'other.test', 'a.test', 'b.test', 'saas.test', 'testserver', 'localhost', '127.0.0.1', 'unknown.test'),
)
class TenantDbIsolationTests(TestCase):
    databases = {'default', 'tenant_1', 'tenant_2'}

    def setUp(self):
        super().setUp()
        self.factory = APIRequestFactory()
        self.client = APIClient()

        self.tenant_a = Tenant.objects.create(
            id=1, name='Complex A', slug='a', db_name='lab_test', is_active=True
        )
        self.tenant_b = Tenant.objects.create(
            id=2, name='Complex B', slug='b', db_name='other_test', is_active=True
        )
        Domain.objects.create(tenant=self.tenant_a, hostname='a.test', is_primary=True)
        Domain.objects.create(tenant=self.tenant_b, hostname='b.test', is_primary=True)

        self.alias_a = connections.ensure_tenant_connection(self.tenant_a)
        self.alias_b = connections.ensure_tenant_connection(self.tenant_b)

        context.set_current_tenant(self.tenant_a, alias=self.alias_a)
        try:
            self.owner_a = Staff.objects.create(
                email='owner-a@test.com',
                password=make_password('x'),
                role=StaffRole.OWNER,
                is_active=True,
            )
            self.staff_a = Staff.objects.create(
                email='a@test.com',
                password=make_password('x'),
                role=StaffRole.FRONT_DESK,
                is_active=True,
            )
        finally:
            context.clear_current_tenant()

        context.set_current_tenant(self.tenant_b, alias=self.alias_b)
        try:
            self.staff_b = Staff.objects.create(
                email='b@test.com',
                password=make_password('x'),
                role=StaffRole.FRONT_DESK,
                is_active=True,
            )
        finally:
            context.clear_current_tenant()

    def test_list_only_sees_active_tenant_db(self):
        context.set_current_tenant(self.tenant_a, alias=self.alias_a)
        try:
            req = self.factory.get('/api/staff/')
            force_authenticate(req, user=self.owner_a)
            req.is_central = False
            req.tenant = self.tenant_a
            view = StaffViewSet.as_view({'get': 'list'})
            response = view(req)
            emails = {row['email'] for row in response.data['data']}
            self.assertIn('a@test.com', emails)
            self.assertNotIn('b@test.com', emails)
        finally:
            context.clear_current_tenant()

    def test_staff_list_denied_to_front_desk(self):
        # Staff administration is owner-only since TODO-06.
        context.set_current_tenant(self.tenant_a, alias=self.alias_a)
        try:
            req = self.factory.get('/api/staff/')
            force_authenticate(req, user=self.staff_a)
            req.is_central = False
            req.tenant = self.tenant_a
            view = StaffViewSet.as_view({'get': 'list'})
            response = view(req)
            self.assertEqual(response.status_code, 403)
        finally:
            context.clear_current_tenant()

    def test_unknown_host_404(self):
        response = self.client.get('/api/health/', HTTP_HOST='unknown.test')
        self.assertEqual(response.status_code, 404)

    def test_health_on_known_tenant_host(self):
        response = self.client.get('/api/health/', HTTP_HOST='a.test')
        self.assertEqual(response.status_code, 200)
