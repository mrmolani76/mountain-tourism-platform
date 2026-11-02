"""
Auth API tests — item 05.
Login / me / logout / password reset + 401/403 authz.
"""
from django.test import TestCase
from rest_framework.test import APIClient
from django.contrib.auth.hashers import make_password

from apps.core.models import Staff, StaffRole, Tenant


class AuthTests(TestCase):
    def setUp(self):
        self.client = APIClient()
        self.tenant = Tenant.objects.create(name='Test Complex', is_active=True)

        self.owner = Staff.objects.create(
            email='owner@test.com',
            password=make_password('ownerpass'),
            role=StaffRole.OWNER,
            tenant=self.tenant,
            is_active=True,
        )
        self.manager = Staff.objects.create(
            email='manager@test.com',
            password=make_password('managerpass'),
            role=StaffRole.MANAGER,
            tenant=self.tenant,
            is_active=True,
        )
        self.staff = Staff.objects.create(
            email='staff@test.com',
            password=make_password('staffpass'),
            role=StaffRole.FRONT_DESK,
            tenant=self.tenant,
            is_active=True,
        )
        self.inactive_staff = Staff.objects.create(
            email='inactive@test.com',
            password=make_password('pass'),
            role=StaffRole.FRONT_DESK,
            tenant=self.tenant,
            is_active=False,
        )

    def test_login_success(self):
        response = self.client.post('/api/auth/login/', {
            'email': self.owner.email, 'password': 'ownerpass'
        }, format='json')
        self.assertEqual(response.status_code, 200)
        self.assertEqual(self.client.session.get('staff_id'), self.owner.id)

    def test_login_wrong_password(self):
        response = self.client.post('/api/auth/login/', {
            'email': self.owner.email, 'password': 'wrongpass'
        }, format='json')
        self.assertEqual(response.status_code, 401)

    def test_login_unknown_email(self):
        response = self.client.post('/api/auth/login/', {
            'email': 'nobody@test.com', 'password': 'x'
        }, format='json')
        self.assertEqual(response.status_code, 401)

    def test_login_inactive_staff_denied(self):
        response = self.client.post('/api/auth/login/', {
            'email': self.inactive_staff.email, 'password': 'pass'
        }, format='json')
        self.assertEqual(response.status_code, 403)

    def test_me_returns_staff(self):
        self.client.force_authenticate(user=self.owner)
        response = self.client.get('/api/auth/me/')
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.data['email'], self.owner.email)
        self.assertEqual(response.data['role'], 'Owner')

    def test_me_without_auth_denied(self):
        self.client.force_authenticate(user=None)
        response = self.client.get('/api/auth/me/')
        self.assertIn(response.status_code, (401, 403))

    def test_logout(self):
        self.client.force_authenticate(user=self.owner)
        response = self.client.post('/api/auth/logout/')
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.data['detail'], 'Logged out.')

    def test_password_reset_by_owner(self):
        self.client.force_authenticate(user=self.owner)
        response = self.client.post('/api/auth/reset-password/', {
            'staff_id': self.staff.id, 'new_password': 'newsecret123'
        }, format='json')
        self.assertEqual(response.status_code, 200)
        self.staff.refresh_from_db()
        self.assertTrue(self.staff.password.startswith('pbkdf2_sha256'))

    def test_password_reset_by_manager(self):
        self.client.force_authenticate(user=self.manager)
        response = self.client.post('/api/auth/reset-password/', {
            'staff_id': self.staff.id, 'new_password': 'newsecret123'
        }, format='json')
        self.assertEqual(response.status_code, 200)

    def test_password_reset_by_front_desk_403(self):
        self.client.force_authenticate(user=self.staff)
        response = self.client.post('/api/auth/reset-password/', {
            'staff_id': self.staff.id, 'new_password': 'newsecret123'
        }, format='json')
        self.assertEqual(response.status_code, 403)

    def test_password_reset_cross_tenant_403(self):
        other_tenant = Tenant.objects.create(name='Other', is_active=True)
        other_staff = Staff.objects.create(
            email='other@test.com', password=make_password('pass'),
            role=StaffRole.FRONT_DESK, tenant=other_tenant, is_active=True,
        )
        self.client.force_authenticate(user=self.owner)
        response = self.client.post('/api/auth/reset-password/', {
            'staff_id': other_staff.id, 'new_password': 'newsecret123'
        }, format='json')
        self.assertEqual(response.status_code, 403)
