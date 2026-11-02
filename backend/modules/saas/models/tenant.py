"""Central tenant registry + hostname map (landlord DB only)."""

from django.db import models


class Tenant(models.Model):
    """One complex. Physical data lives in db_name (separate Postgres DB)."""

    name = models.CharField(max_length=200)
    slug = models.SlugField(max_length=64, unique=True)
    db_name = models.CharField(max_length=128, unique=True)
    is_active = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'saas_tenant'
        ordering = ['name']

    def __str__(self):
        return f'{self.name} ({self.slug})'


class Domain(models.Model):
    """Hostname → tenant (e.g. lab.local.mountain.local)."""

    tenant = models.ForeignKey(
        Tenant, on_delete=models.CASCADE, related_name='domains'
    )
    hostname = models.CharField(max_length=255, unique=True)
    is_primary = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        db_table = 'saas_domain'
        ordering = ['hostname']

    def __str__(self):
        return self.hostname
