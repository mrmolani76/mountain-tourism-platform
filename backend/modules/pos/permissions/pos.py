"""POS permissions — role-based access."""

from rest_framework.permissions import BasePermission


class IsPosStaff(BasePermission):
    """Any authenticated tenant staff can read POS data."""

    def has_permission(self, request, view):
        user = getattr(request, 'user', None)
        return bool(
            user
            and getattr(user, 'is_authenticated', False)
            and user.__class__.__name__ == 'Staff'
        )


class IsPosCashier(BasePermission):
    """Cashier, front desk, owner, manager can create orders."""

    def has_permission(self, request, view):
        user = getattr(request, 'user', None)
        return bool(
            user
            and getattr(user, 'is_authenticated', False)
            and user.__class__.__name__ == 'Staff'
            and getattr(user, 'role', None) in (
                'owner', 'manager', 'front_desk', 'cashier'
            )
        )
