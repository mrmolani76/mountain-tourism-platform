"""
Register a tenant on the central DB (code path). Does not createdb/migrate.

Usage (when ops later):
  python manage.py provision_tenant --name "Lab" --slug lab
"""

from django.core.management.base import BaseCommand

from tenancy.provision import register_tenant


class Command(BaseCommand):
    help = 'Register tenant + domain on central DB (no physical DB create).'

    def add_arguments(self, parser):
        parser.add_argument('--name', required=True)
        parser.add_argument('--slug', required=True)
        parser.add_argument('--env', default='local')
        parser.add_argument('--base-domain', default='local.mountain.local')

    def handle(self, *args, **options):
        tenant = register_tenant(
            name=options['name'],
            slug=options['slug'],
            env=options['env'],
            base_domain=options['base_domain'],
        )
        self.stdout.write(self.style.SUCCESS(
            f'Registered tenant id={tenant.pk} slug={tenant.slug} db_name={tenant.db_name}'
        ))
        self.stdout.write('Physical createdb/migrate not run (code-only policy).')
