"""Stay services — check-in/check-out."""

from django.core.exceptions import ValidationError

from modules.hotel.models import Stay


def check_in_stay(*, stay: Stay, actor) -> Stay:
    """Check in a stay."""
    if stay.status != Stay.StayStatus.RESERVED:
        raise ValidationError('Only reserved stays can be checked in.')
    stay.status = Stay.StayStatus.CHECKED_IN
    stay.save(update_fields=['status', 'updated_at'])
    return stay


def check_out_stay(*, stay: Stay, actor) -> Stay:
    """Check out a stay."""
    if stay.status != Stay.StayStatus.CHECKED_IN:
        raise ValidationError('Only checked-in stays can be checked out.')
    stay.status = Stay.StayStatus.CHECKED_OUT
    stay.save(update_fields=['status', 'updated_at'])
    return stay
