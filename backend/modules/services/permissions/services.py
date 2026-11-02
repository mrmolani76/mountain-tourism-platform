"""Services permissions."""

from rest_framework.permissions import BasePermission


class IsServicesStaff(BasePermission):
    """Any authenticated tenant staff can access services data."""

    def has_permission(self, request, view):
        user = getattr(request, 'user', None)
        return bool(
            user
            and getattr(user, 'is_authenticated', False)
            and user.__class__.__name__ == 'Staff'
        )
