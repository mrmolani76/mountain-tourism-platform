"""Transport permissions."""

from rest_framework.permissions import BasePermission


class IsTransportStaff(BasePermission):
    """Any authenticated tenant staff can access transport data."""

    def has_permission(self, request, view):
        user = getattr(request, 'user', None)
        return bool(
            user
            and getattr(user, 'is_authenticated', False)
            and user.__class__.__name__ == 'Staff'
        )
