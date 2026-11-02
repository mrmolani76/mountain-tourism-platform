"""
Cross-tenant isolation test — Module A acceptance (PDF §5A done when).

Proves two complexes on the same server cannot see each other's data
via the API (list + detail), enforced by the single TenantManager layer.

The TenantContextMiddleware sets the current tenant from the authenticated
staff user on every real request. In tests we simulate that authenticated
context directly (force_authenticate sets request.user after middleware, so
we set the tenant context explicitly to mirror production behaviour).
"""
from django.test import TestCase, override_settings
from rest_framework.test import APIRequestFactory, force_authenticate

from apps.core.models import Tenant, Staff
from apps.core.models.base import set_current_tenant, clear_current_tenant
from apps.core import views


@override_settings(ROOT_URLCONF='config.urls')
class TenantIsolationTests(TestCase):
    def setUp(self):
        self.factory = APIRequestFactory()
        self.tenant_a = Tenant.objects.create(name='Complex A', is_active=True)
        self.tenant_b = Tenant.objects.create(name='Complex B', is_active=True)

        self.staff_a1 = Staff.objects.create(
            email='a1@complex-a.test', password='x',
            tenant=self.tenant_a, is_active=True,
        )
        self.staff_a2 = Staff.objects.create(
            email='a2@complex-a.test', password='x',
            tenant=self.tenant_a, is_active=True,
        )
        self.staff_b1 = Staff.objects.create(
            email='b1@complex-b.test', password='x',
            tenant=self.tenant_b, is_active=True,
        )

    def _request(self, method, path, user, data=None):
        """Build an authenticated request with the tenant context set."""
        req = getattr(self.factory, method)(path, data=data, format='json')
        force_authenticate(req, user=user)
        set_current_tenant(user.tenant)
        return req

    def tearDown(self):
        clear_current_tenant()

    def test_cross_tenant_list_denied(self):
        """Staff of tenant A must not list tenant B's staff."""
        req = self._request('get', '/api/staff/', self.staff_a1)
        response = views.staff_list(req)
        self.assertEqual(response.status_code, 200)
        emails = [item['email'] for item in response.data]
        self.assertIn(self.staff_a1.email, emails)
        self.assertIn(self.staff_a2.email, emails)
        self.assertNotIn(self.staff_b1.email, emails)
        self.assertEqual(len(emails), 2)

    def test_cross_tenant_detail_denied(self):
        """Staff of tenant A must not retrieve tenant B's staff by id."""
        req = self._request('get', f'/api/staff/{self.staff_b1.pk}/', self.staff_a1)
        response = views.staff_detail(req, pk=self.staff_b1.pk)
        self.assertEqual(response.status_code, 404)

    def test_own_tenant_detail_allowed(self):
        """Staff of tenant A can retrieve their own tenant's staff."""
        req = self._request('get', f'/api/staff/{self.staff_a2.pk}/', self.staff_a1)
        response = views.staff_detail(req, pk=self.staff_a2.pk)
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.data['email'], self.staff_a2.email)

    def test_no_tenant_context_returns_none(self):
        """Authenticated but no tenant context → tenant-owned queries return none."""
        clear_current_tenant()
        req = self.factory.get('/api/staff/')
        force_authenticate(req, user=self.staff_a1)  # authenticated, no tenant ctx
        response = views.staff_list(req)
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.data, [])

    def test_health_endpoint_public(self):
        """Health is public (no auth / no tenant)."""
        req = self.factory.get('/api/health/')
        response = views.health(req)
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.data['status'], 'ok')
