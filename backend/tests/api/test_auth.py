"""
Auth API tests — tenant host, DRF session auth.
Code written for DB-per-tenant; not executed in this work item (code-only).
"""

from types import SimpleNamespace

from django.conf import settings
from django.contrib.auth.hashers import make_password
from django.db import connections as db_connections
from django.test import TestCase, override_settings
from rest_framework.test import APIClient

from modules.core.models import Staff, StaffRole
from modules.saas.models import Domain, Tenant
from tenancy import connections, context
from tenancy.connections import tenant_alias


OTHER_TENANT_HOST = {'HTTP_HOST': 'other.test'}


@override_settings(
    TENANCY_CENTRAL_HOSTS=('saas.test',),
    TENANCY_CENTRAL_PREFIX='saas.',
)
class AuthTests(TestCase):
    databases = {'default', 'tenant_1', 'tenant_2'}

    # Aliases used by this class (tenant pks are deterministic in the test DB:
    # each test's transaction rollback reuses pk 1 and 2).
    TENANT_ALIASES = ((1, 'lab_test'), (2, 'other_test'))

    @classmethod
    def setUpClass(cls):
        # Register the tenant aliases this class needs before Django's class
        # init machinery (literal READ of settings + alias TEST.MIRROR check)
        # runs. Aliases created here later get a live cursor via the TestCase
        # transaction wrapper; aliases left stale by other test classes get
        # their db_name re-pointed but the underlying sqlite file is shared.
        for pk, db_name in cls.TENANT_ALIASES:
            alias = tenant_alias(pk)
            if alias in db_connections.databases:
                # Re-point to this class's db_name (shared sqlite file).
                cfg = db_connections.databases[alias]
                cfg['NAME'] = db_name
                sc = settings.DATABASES.get(alias, cfg)
                sc['NAME'] = db_name
            else:
                connections.ensure_tenant_connection(
                    SimpleNamespace(pk=pk, db_name=db_name)
                )
            tc = settings.DATABASES[alias].setdefault('TEST', {})
            tc.setdefault('MIRROR', None)
            tcfg = db_connections.databases.setdefault(alias, {})
            tcfg.setdefault('TEST', {}).setdefault('MIRROR', None)
        super().setUpClass()

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

        # Second tenant + a colliding staff pk for cross-host replay tests.
        self.other_tenant = Tenant.objects.create(
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
        self.client.force_login(self.owner)
        s = self.client.session
        s.pop('staff_tenant_id', None)
        s.pop('staff_tenant_slug', None)
        s.save()
        response = self.client.get('/api/auth/me/', **self.tenant_host)
        self.assertIn(response.status_code, (401, 403))
