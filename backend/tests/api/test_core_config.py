"""
Core configuration APIs — settings, buildings, outlets, seasons, staff admin
(TODO-06). Code written for DB-per-tenant; not executed in this work item.

Cross-tenant proof: owner of tenant A gets 404/403 for tenant B resources.
"""

from datetime import date

from django.contrib.auth.hashers import make_password
from django.test import TestCase, override_settings
from rest_framework.test import APIClient

from modules.core.models import Building, Season, Staff, StaffRole
from modules.saas.models import Domain, Tenant
from tenancy import connections, context


@override_settings(
    TENANCY_CENTRAL_HOSTS=('saas.test',),
    TENANCY_CENTRAL_PREFIX='saas.',
)
class CoreConfigTests(TestCase):
    """Code-only suite: needs provisioned + migrated tenant DBs on setup machine."""

    databases = {'default'}

    def setUp(self):
        self.client = APIClient()

        self.tenant = Tenant.objects.create(
            name='Test Complex', slug='lab', db_name='lab_test', is_active=True
        )
        Domain.objects.create(tenant=self.tenant, hostname='lab.test', is_primary=True)
        other = Tenant.objects.create(
            name='Other Complex', slug='other', db_name='other_test', is_active=True
        )
        Domain.objects.create(tenant=other, hostname='other.test', is_primary=True)

        self.alias = connections.ensure_tenant_connection(self.tenant)
        self.other_alias = connections.ensure_tenant_connection(other)

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
            self.building = Building.objects.create(
                name='Main Hotel',
                building_type='main_hotel',
                altitude_m=1200,
                latitude='46.771221',
                longitude='10.363433',
            )
        finally:
            context.clear_current_tenant()

        context.set_current_tenant(other, alias=self.other_alias)
        try:
            self.other_building = Building.objects.create(
                name='Other Hotel', building_type='main_hotel', altitude_m=900
            )
        finally:
            context.clear_current_tenant()

        self.tenant_host = {'HTTP_HOST': 'lab.test'}

    # --- tenant settings -------------------------------------------------

    def test_settings_get_by_owner(self):
        self.client.force_authenticate(user=self.owner)
        response = self.client.get('/api/settings/', **self.tenant_host)
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.data['currency'], 'EUR')
        self.assertEqual(response.data['units'], 'metric')

    def test_settings_patch_by_owner(self):
        self.client.force_authenticate(user=self.owner)
        response = self.client.patch(
            '/api/settings/',
            {'currency': 'CHF', 'units': 'imperial', 'tax_rates': [8.1]},
            format='json',
            **self.tenant_host,
        )
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.data['currency'], 'CHF')
        self.assertEqual(response.data['tax_rates'], [8.1])

    def test_settings_denied_for_manager(self):
        self.client.force_authenticate(user=self.manager)
        response = self.client.get('/api/settings/', **self.tenant_host)
        self.assertEqual(response.status_code, 403)

    def test_settings_denied_for_front_desk(self):
        self.client.force_authenticate(user=self.staff)
        response = self.client.patch(
            '/api/settings/', {'currency': 'CHF'}, format='json', **self.tenant_host
        )
        self.assertEqual(response.status_code, 403)

    # --- buildings --------------------------------------------------------

    def test_building_create_and_list_by_owner(self):
        self.client.force_authenticate(user=self.owner)
        response = self.client.post(
            '/api/buildings/',
            {
                'name': 'Mountain Hut',
                'building_type': 'mountain_hut',
                'altitude_m': 2100,
                'latitude': '46.765000',
                'longitude': '10.350000',
            },
            format='json',
            **self.tenant_host,
        )
        self.assertEqual(response.status_code, 201)
        self.assertEqual(response.data['altitude_m'], 2100)

        listing = self.client.get('/api/buildings/', **self.tenant_host)
        self.assertEqual(listing.status_code, 200)
        names = {row['name'] for row in listing.data}
        self.assertEqual(names, {'Main Hotel', 'Mountain Hut'})

    def test_building_patch(self):
        self.client.force_authenticate(user=self.owner)
        response = self.client.patch(
            f'/api/buildings/{self.building.id}/',
            {'altitude_m': 1205},
            format='json',
            **self.tenant_host,
        )
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.data['altitude_m'], 1205)

    def test_building_delete_by_owner(self):
        self.client.force_authenticate(user=self.owner)
        response = self.client.delete(
            f'/api/buildings/{self.building.id}/', **self.tenant_host
        )
        self.assertEqual(response.status_code, 204)

    def test_buildings_denied_for_manager(self):
        self.client.force_authenticate(user=self.manager)
        response = self.client.get('/api/buildings/', **self.tenant_host)
        self.assertEqual(response.status_code, 403)

    # --- outlets ----------------------------------------------------------

    def test_outlet_crud_by_owner(self):
        self.client.force_authenticate(user=self.owner)
        response = self.client.post(
            '/api/outlets/',
            {
                'building': self.building.id,
                'name': 'Panorama Restaurant',
                'outlet_type': 'panorama_restaurant',
            },
            format='json',
            **self.tenant_host,
        )
        self.assertEqual(response.status_code, 201)
        self.assertEqual(response.data['building_name'], 'Main Hotel')

        detail = self.client.get(
            f"/api/outlets/{response.data['id']}/", **self.tenant_host
        )
        self.assertEqual(detail.status_code, 200)

        update = self.client.patch(
            f"/api/outlets/{response.data['id']}/",
            {'name': 'Panorama Bar'},
            format='json',
            **self.tenant_host,
        )
        self.assertEqual(update.status_code, 200)
        self.assertEqual(update.data['name'], 'Panorama Bar')

        delete = self.client.delete(
            f"/api/outlets/{response.data['id']}/", **self.tenant_host
        )
        self.assertEqual(delete.status_code, 204)

    def test_outlet_missing_building_400(self):
        self.client.force_authenticate(user=self.owner)
        response = self.client.post(
            '/api/outlets/',
            {'name': 'No Building Outlet', 'outlet_type': 'shop'},
            format='json',
            **self.tenant_host,
        )
        self.assertEqual(response.status_code, 400)

    # --- seasons ----------------------------------------------------------

    def test_season_create_by_owner(self):
        self.client.force_authenticate(user=self.owner)
        response = self.client.post(
            '/api/seasons/',
            {'kind': 'winter', 'starts_on': '2026-12-01', 'ends_on': '2027-04-15'},
            format='json',
            **self.tenant_host,
        )
        self.assertEqual(response.status_code, 201)
        self.assertEqual(response.data['kind'], 'winter')

    def test_season_overlap_rejected(self):
        self.client.force_authenticate(user=self.owner)
        Season.objects.create(
            kind='winter', starts_on=date(2026, 12, 1), ends_on=date(2027, 4, 15)
        )
        response = self.client.post(
            '/api/seasons/',
            {'kind': 'summer', 'starts_on': '2027-04-01', 'ends_on': '2027-10-31'},
            format='json',
            **self.tenant_host,
        )
        self.assertEqual(response.status_code, 400)

    def test_season_bad_dates_rejected(self):
        self.client.force_authenticate(user=self.owner)
        response = self.client.post(
            '/api/seasons/',
            {'kind': 'summer', 'starts_on': '2027-10-31', 'ends_on': '2027-04-01'},
            format='json',
            **self.tenant_host,
        )
        self.assertEqual(response.status_code, 400)

    # --- staff admin ------------------------------------------------------

    def test_staff_crud_by_owner(self):
        self.client.force_authenticate(user=self.owner)
        response = self.client.post(
            '/api/staff/',
            {
                'email': 'new-staff@test.com',
                'role': 'cashier',
                'is_active': True,
            },
            format='json',
            **self.tenant_host,
        )
        self.assertEqual(response.status_code, 201)

        listing = self.client.get('/api/staff/', **self.tenant_host)
        self.assertEqual(listing.status_code, 200)
        self.assertGreaterEqual(len(listing.data), 4)

    def test_staff_access_denied_for_front_desk(self):
        self.client.force_authenticate(user=self.staff)
        response = self.client.get('/api/staff/', **self.tenant_host)
        self.assertEqual(response.status_code, 403)

    def test_outlet_access_assign_by_owner(self):
        self.client.force_authenticate(user=self.owner)
        response = self.client.post(
            '/api/outlet-access/',
            {'staff': self.staff.id, 'outlet': None},
            format='json',
            **self.tenant_host,
        )
        # Outlet table empty in this tenant: expect validation error, not 403.
        self.assertIn(response.status_code, (201, 400))

    # --- cross-tenant denial ---------------------------------------------

    def test_owner_cannot_reach_other_tenant_building(self):
        self.client.force_authenticate(user=self.owner)
        response = self.client.get(
            f'/api/buildings/{self.other_building.id}/', **self.tenant_host
        )
        self.assertEqual(response.status_code, 404)
