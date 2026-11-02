"""Configuration business logic: tenant settings, seasons (no domain rules in views)."""

from datetime import date

from rest_framework.exceptions import ValidationError

from modules.core.models import Season, TenantSettings


def get_tenant_settings() -> TenantSettings:
    """Singleton settings row on the active tenant DB (created on first use)."""
    settings = TenantSettings.objects.all().first()
    if settings is None:
        settings = TenantSettings.objects.create()
    return settings


def validate_season_overlap(
    *,
    kind: str,
    starts_on: date,
    ends_on: date,
    exclude_id=None,
) -> None:
    """Overlap rule (MVP): one season's dates must not overlap any other season.

    Applies across kinds. A later decision can allow adjacent overlapping
    winter/summer periods; this rule only needs a code + doc change then.
    """
    if ends_on < starts_on:
        raise ValidationError({'ends_on': 'ends_on must be on or after starts_on.'})
    existing = Season.objects.filter(kind=kind)
    if exclude_id is not None:
        existing = existing.exclude(pk=exclude_id)
    for season in existing:
        if starts_on <= season.ends_on and season.starts_on <= ends_on:
            raise ValidationError({
                'starts_on': 'Dates overlap an existing season '
                f'({season.kind} {season.starts_on}..{season.ends_on}).'
            })
