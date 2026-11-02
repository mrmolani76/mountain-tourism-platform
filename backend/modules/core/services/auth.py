"""Auth business logic for tenant Staff."""

from django.contrib.auth.hashers import check_password, make_password
from rest_framework.exceptions import AuthenticationFailed, NotFound, PermissionDenied

from modules.core.models import Staff
from tenancy.context import get_current_tenant


def authenticate_staff(*, email: str, password: str) -> Staff:
    try:
        staff = Staff.objects.get(email=email)
    except Staff.DoesNotExist:
        raise AuthenticationFailed('Invalid credentials.')
    if not check_password(password, staff.password):
        raise AuthenticationFailed('Invalid credentials.')
    if not staff.is_active:
        raise PermissionDenied('Staff inactive.')
    tenant = get_current_tenant()
    if tenant is not None and not tenant.is_active:
        raise PermissionDenied('Tenant inactive.')
    return staff


def reset_staff_password(*, actor: Staff, staff_id: int, new_password: str) -> Staff:
    if not actor.can_reset_password:
        raise PermissionDenied('Only owner/manager can reset passwords.')
    try:
        target = Staff.objects.get(pk=staff_id)
    except Staff.DoesNotExist:
        raise NotFound('Staff not found.')
    target.password = make_password(new_password)
    target.save(update_fields=['password'])
    return target
