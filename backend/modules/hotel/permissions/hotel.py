"""Hotel permissions — role-based access."""

from rest_framework.permissions import BasePermission


class IsHotelStaff(BasePermission):
    """Any authenticated tenant staff can read hotel data."""

    def has_permission(self, request, view):
        user = getattr(request, 'user', None)
        return bool(
            user
            and getattr(user, 'is_authenticated', False)
            and user.__class__.__name__ == 'Staff'
        )


class IsHotelManager(BasePermission):
    """Owner or manager can create/modify stays and rooms."""

    def has_permission(self, request, view):
        user = getattr(request, 'user', None)
        return bool(
            user
            and getattr(user, 'is_authenticated', False)
            and user.__class__.__name__ == 'Staff'
            and getattr(user, 'role', None) in ('owner', 'manager')
        )
