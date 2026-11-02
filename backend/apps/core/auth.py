"""Session-based auth for custom Staff model (not Django's User)."""

from django.contrib.auth.hashers import check_password
from rest_framework.authentication import BaseAuthentication
from rest_framework.exceptions import AuthenticationFailed

from .models import Staff


class StaffSessionAuthentication(BaseAuthentication):
    """
    Authenticates via staff_id stored in the Django session (set by login view).
    Returns (Staff, None) so DRF permission classes work normally.
    """

    def authenticate(self, request):
        session = getattr(request, 'session', None)
        if not session:
            return None
        staff_id = session.get('staff_id')
        if not staff_id:
            return None
        try:
            staff = Staff.objects.get(pk=staff_id)
        except Staff.DoesNotExist:
            raise AuthenticationFailed('Invalid session.')
        if not staff.is_active:
            raise AuthenticationFailed('Staff inactive.')
        if not staff.tenant.is_active:
            raise AuthenticationFailed('Tenant inactive.')
        return (staff, None)
