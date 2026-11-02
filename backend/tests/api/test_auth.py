"""
Auth API tests — tenant host, DRF session auth.
Code written for DB-per-tenant; not executed in this work item (code-only).
"""

from django.contrib.auth.hashers import make_password
from django.test import TestCase, override_settings
from rest_framework.test import APIClient

from modules.core.models import Staff, StaffRole
from modules.saas.models import Domain, Tenant
from tenancy import connections, context


@override_settings(
    TENANCY_CENTRAL_HOSTS=('saas.test',),
    TENANCY_CENTRAL_PREFIX='saas.',
)
class AuthTests(TestCase):
    databases = {'default'}

    def setUp(self):
        self.client = APIClient()
        self.tenant = Tenant.objects.create(
            name='Test Complex',
            slug='lab',
            db_name='lab_test',
            is_active=True,
        )
        Domain.objects.create(
            tenant=self.tenant,
            hostname='lab.test',
            is_primary=True,
        )
        self.alias = connections.ensure_tenant_connection(self.tenant)
        # When the physical tenant DB exists, staff rows live there.
        # Until setup runs, tests hitting the tenant DB need that alias migrated.
        context.set_current_tenant(self.tenant, alias=self.alias)
        try:
            self.owner = Staff.objects.create(
                email='owner@test.com',
                password=make_password('ownerpass'),
                role=StaffRole.OWNER,
                is_active=True,
            )
            self.manager = Staff.objects.create(
                email='manager@test.com',
                password=make_password('managerpass'),
                role=StaffRole.MANAGER,
                is_active=True,
            )
            self.staff = Staff.objects.create(
                email='staff@test.com',
                password=make_password('staffpass'),
                role=StaffRole.FRONT_DESK,
                is_active=True,
            )
            self.inactive_staff = Staff.objects.create(
                email='inactive@test.com',
                password=make_password('pass'),
                role=StaffRole.FRONT_DESK,
                is_active=False,
            )
        finally:
            context.clear_current_tenant()

        self.tenant_host = {'HTTP_HOST': 'lab.test'}

    def test_login_success(self):
        response = self.client.post(
            '/api/auth/login/',
            {'email': self.owner.email, 'password': 'ownerpass'},
            format='json',
            **self.tenant_host,
        )
        self.assertEqual(response.status_code, 200)
        self.assertEqual(self.client.session.get('staff_id'), self.owner.id)

    def test_login_wrong_password(self):
        response = self.client.post(
            '/api/auth/login/',
            {'email': self.owner.email, 'password': 'wrongpass'},
            format='json',
            **self.tenant_host,
        )
        self.assertEqual(response.status_code, 401)

    def test_login_unknown_email(self):
        response = self.client.post(
            '/api/auth/login/',
            {'email': 'nobody@test.com', 'password': 'x'},
            format='json',
            **self.tenant_host,
        )
        self.assertEqual(response.status_code, 401)

    def test_login_inactive_staff_denied(self):
        response = self.client.post(
            '/api/auth/login/',
            {'email': self.inactive_staff.email, 'password': 'pass'},
            format='json',
            **self.tenant_host,
        )
        self.assertEqual(response.status_code, 403)

    def test_me_returns_staff(self):
        self.client.force_authenticate(user=self.owner)
        response = self.client.get('/api/auth/me/', **self.tenant_host)
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.data['email'], self.owner.email)
        self.assertEqual(response.data['role'], 'Owner')

    def test_me_without_auth_denied(self):
        self.client.force_authenticate(user=None)
        response = self.client.get('/api/auth/me/', **self.tenant_host)
        self.assertIn(response.status_code, (401, 403))

    def test_logout(self):
        self.client.force_authenticate(user=self.owner)
        response = self.client.post('/api/auth/logout/', **self.tenant_host)
        self.assertEqual(response.status_code, 200)

    def test_password_reset_by_owner(self):
        self.client.force_authenticate(user=self.owner)
        response = self.client.post(
            '/api/auth/reset-password/',
            {'staff_id': self.staff.id, 'new_password': 'newsecret123'},
            format='json',
            **self.tenant_host,
        )
        self.assertEqual(response.status_code, 200)

    def test_password_reset_by_manager(self):
        self.client.force_authenticate(user=self.manager)
        response = self.client.post(
            '/api/auth/reset-password/',
            {'staff_id': self.staff.id, 'new_password': 'newsecret123'},
            format='json',
            **self.tenant_host,
        )
        self.assertEqual(response.status_code, 200)

    def test_password_reset_by_front_desk_403(self):
        self.client.force_authenticate(user=self.staff)
        response = self.client.post(
            '/api/auth/reset-password/',
            {'staff_id': self.staff.id, 'new_password': 'newsecret123'},
            format='json',
            **self.tenant_host,
        )
        self.assertEqual(response.status_code, 403)
