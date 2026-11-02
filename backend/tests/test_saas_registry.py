"""
Central SaaS DRF API — tenant registry (no physical DB create).
"""

from django.contrib.auth.hashers import make_password
from django.test import TestCase, override_settings
from rest_framework.test import APIClient

from modules.saas.models import SaasUser, Tenant


@override_settings(
    TENANCY_CENTRAL_HOSTS=('saas.test', 'localhost', '127.0.0.1', 'testserver'),
    TENANCY_CENTRAL_PREFIX='saas.',
)
class SaasRegistryTests(TestCase):
    databases = {'default'}

    def setUp(self):
        self.client = APIClient()
        self.admin = SaasUser.objects.create(
            email='admin@saas.test',
            password=make_password('adminpass'),
            is_active=True,
        )
        self.central_host = {'HTTP_HOST': 'saas.test'}

    def test_saas_login(self):
        response = self.client.post(
            '/api/saas/auth/login/',
            {'email': self.admin.email, 'password': 'adminpass'},
            format='json',
            **self.central_host,
        )
        self.assertEqual(response.status_code, 200)
        self.assertEqual(self.client.session.get('saas_user_id'), self.admin.id)

    def test_register_tenant_registry_only(self):
        self.client.force_authenticate(user=self.admin)
        response = self.client.post(
            '/api/saas/tenants/',
            {'name': 'Lab', 'slug': 'lab', 'base_domain': 'test'},
            format='json',
            **self.central_host,
        )
        self.assertEqual(response.status_code, 201)
        self.assertEqual(response.data['slug'], 'lab')
        self.assertEqual(response.data['db_name'], 'lab_local')
        self.assertTrue(Tenant.objects.filter(slug='lab').exists())

    def test_deactivate_tenant(self):
        tenant = Tenant.objects.create(
            name='X', slug='x', db_name='x_local', is_active=True
        )
        self.client.force_authenticate(user=self.admin)
        response = self.client.post(
            f'/api/saas/tenants/{tenant.pk}/deactivate/',
            **self.central_host,
        )
        self.assertEqual(response.status_code, 200)
        tenant.refresh_from_db()
        self.assertFalse(tenant.is_active)
