"""
Stub command: would migrate all registered tenant DBs.

Does not run migrations (code-only / no setup).
"""

from django.core.management.base import BaseCommand

from modules.saas.models import Tenant
from tenancy.connections import ensure_tenant_connection


class Command(BaseCommand):
    help = 'List tenant DB aliases that would be migrated (does not migrate).'

    def add_arguments(self, parser):
        parser.add_argument(
            '--execute',
            action='store_true',
            help='Reserved for future; currently refused.',
        )

    def handle(self, *args, **options):
        if options['execute']:
            self.stderr.write(self.style.ERROR(
                'Refusing --execute: physical migrate is setup, not run from this stub.'
            ))
            return

        for tenant in Tenant.objects.filter(is_active=True):
            alias = ensure_tenant_connection(tenant)
            self.stdout.write(f'would migrate alias={alias} db_name={tenant.db_name}')
        self.stdout.write('Dry-run only. No migrations applied.')
