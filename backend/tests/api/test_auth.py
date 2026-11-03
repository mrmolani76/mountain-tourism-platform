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


OTHER_TENANT_HOST = {'HTTP_HOST': 'other.test'}


@override_settings(
    TENANCY_CENTRAL_HOSTS=('saas.test',),
    TENANCY_CENTRAL_PREFIX='saas.',
    ALLOWED_HOSTS=('lab.test', 'other.test', 'saas.test', 'testserver', 'localhost', '127.0.0.1'),
)
class AuthTests(TestCase):
    databases = {'default', 'tenant_1', 'tenant_2'}

    def setUp(self):
        self.client = APIClient()
        self.tenant = Tenant.objects.create(
            id=1,
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

        # Second tenant + a colliding staff pk for cross-host replay tests.
        self.other_tenant = Tenant.objects.create(
            id=2,
            name='Other Complex',
            slug='other',
            db_name='other_test',
            is_active=True,
        )
        Domain.objects.create(
            tenant=self.other_tenant, hostname='other.test', is_primary=True
        )
        self.other_alias = connections.ensure_tenant_connection(self.other_tenant)
        context.set_current_tenant(self.other_tenant, alias=self.other_alias)
        try:
            # Same pk as owner in this tenant's DB: old bug turned pk-collision
            # sessions into a silent identity swap on a foreign host.
            self.colliding_staff = Staff.objects.create(
                email='intruder@other.test',
                password=make_password('x'),
                role=StaffRole.FRONT_DESK,
                is_active=True,
            )
        finally:
            context.clear_current_tenant()

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

    # --- Item 31: edge authz -------------------------------------------

    def test_deactivated_tenant_login_denied(self):
        # Deactivated tenant host → middleware refuses before the view.
        self.tenant.is_active = False
        self.tenant.save(update_fields=['is_active'])
        response = self.client.post(
            '/api/auth/login/',
            {'email': self.owner.email, 'password': 'ownerpass'},
            format='json',
            **self.tenant_host,
        )
        self.assertEqual(response.status_code, 404)

    def test_staff_session_rejected_by_saas_endpoint(self):
        # Staff login sets no saas_user_id → SaaS views reject staff (403),
        # keeping super-admin and tenant staff scopes separate.
        self.client.post(
            '/api/auth/login/',
            {'email': self.owner.email, 'password': 'ownerpass'},
            format='json',
            **self.tenant_host,
        )
        response = self.client.get(
            '/api/saas/auth/me/', **{'HTTP_HOST': 'saas.test'}
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

    # --- BUG-TENANT-001: session is bound to the issuing tenant -------------

    def test_login_writes_tenant_binding_to_session(self):
        self.client.post(
            '/api/auth/login/',
            {'email': self.owner.email, 'password': 'ownerpass'},
            format='json',
            **self.tenant_host,
        )
        self.assertEqual(
            self.client.session.get('staff_tenant_id'), self.tenant.pk
        )
        self.assertEqual(
            self.client.session.get('staff_tenant_slug'), self.tenant.slug
        )

    def test_cross_host_session_replay_rejected(self):
        # Log in on tenant A, replay the same cookie on tenant B's host.
        self.client.post(
            '/api/auth/login/',
            {'email': self.owner.email, 'password': 'ownerpass'},
            format='json',
            **self.tenant_host,
        )
        response = self.client.get('/api/auth/me/', **OTHER_TENANT_HOST)
        self.assertIn(response.status_code, (401, 403))

    def test_cross_host_pk_collision_rejected(self):
        # pk collision: staff B has the same pk as owner A. Without the
        # tenant binding this authenticated as the wrong person's row.
        self.client.post(
            '/api/auth/login/',
            {'email': self.owner.email, 'password': 'ownerpass'},
            format='json',
            **self.tenant_host,
        )
        response = self.client.get('/api/auth/me/', **OTHER_TENANT_HOST)
        self.assertIn(response.status_code, (401, 403))

    def test_unbound_session_rejected(self):
        # Simulate a legacy session that predates the tenant binding.
        context.set_current_tenant(self.tenant, alias=self.alias)
        try:
            self.client.force_login(self.owner)
        finally:
            context.clear_current_tenant()
        s = self.client.session
        s.pop('staff_tenant_id', None)
        s.pop('staff_tenant_slug', None)
        s.save()
        response = self.client.get('/api/auth/me/', **self.tenant_host)
        self.assertIn(response.status_code, (401, 403))
