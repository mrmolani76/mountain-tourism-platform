"""Seed demo tenant with PDF §2 buildings/altitudes + sample outlets.

Dev-only. Idempotent: skips existing buildings by name.
Usage: python manage.py seed_foundation
"""

from django.core.management.base import BaseCommand

from modules.core.models import Building, BuildingType, Outlet, OutletType

# PDF §2 layout: Valley 1050, Hotel 1200, Chalets 1250, Mid 1800, Hut 2100, Summit 2600
BUILDINGS = [
    ('Valley Station', BuildingType.VALLEY_STATION, 1050),
    ('Main Hotel', BuildingType.MAIN_HOTEL, 1200),
    ('Chalets', BuildingType.CHALETS, 1250),
    ('Mid Station', BuildingType.MID_STATION, 1800),
    ('Mountain Hut', BuildingType.MOUNTAIN_HUT, 2100),
    ('Summit Station', BuildingType.SUMMIT_STATION, 2600),
]

# Sample outlets per building type
OUTLETS = {
    'Main Hotel': [('Reception', OutletType.RECEPTION), ('Restaurant', OutletType.RESTAURANT)],
    'Mid Station': [('Snack Bar', OutletType.RESTAURANT)],
    'Mountain Hut': [('Kitchen', OutletType.HUT_KITCHEN)],
    'Summit Station': [('Shop', OutletType.SHOP)],
}


class Command(BaseCommand):
    help = 'Seed demo buildings (PDF §2) + sample outlets. Dev only, idempotent.'

    def add_arguments(self, parser):
        parser.add_argument(
            '--reset', action='store_true',
            help='Delete existing buildings/outlets before seeding.',
        )

    def handle(self, *args, **options):
        if options['reset']:
            Outlet.objects.all().delete()
            Building.objects.all().delete()
            self.stdout.write(self.style.WARNING('Reset: all buildings/outlets deleted.'))

        created_buildings = 0
        created_outlets = 0

        for name, btype, altitude in BUILDINGS:
            building, created = Building.objects.get_or_create(
                name=name,
                defaults={'building_type': btype, 'altitude_m': altitude},
            )
            if created:
                created_buildings += 1
                self.stdout.write(f'  Created building: {name} ({altitude}m)')

            # Sample outlets
            for outlet_name, otype in OUTLETS.get(name, []):
                outlet, o_created = Outlet.objects.get_or_create(
                    name=outlet_name,
                    building=building,
                    defaults={'outlet_type': otype, 'is_active': True},
                )
                if o_created:
                    created_outlets += 1
                    self.stdout.write(f'    Created outlet: {outlet_name} in {name}')

        self.stdout.write(self.style.SUCCESS(
            f'Seed complete: {created_buildings} buildings, {created_outlets} outlets created.'
        ))