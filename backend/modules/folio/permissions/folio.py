"""Folio permissions — role-based access."""

from rest_framework.permissions import BasePermission


class IsFolioStaff(BasePermission):
    """Any authenticated tenant staff can read folios/charges/payments."""

    def has_permission(self, request, view):
        user = getattr(request, 'user', None)
        return bool(
            user
            and getattr(user, 'is_authenticated', False)
            and user.__class__.__name__ == 'Staff'
        )


class IsFolioManager(BasePermission):
    """Owner or manager can close folios and void charges/payments."""

    def has_permission(self, request, view):
        user = getattr(request, 'user', None)
        return bool(
            user
            and getattr(user, 'is_authenticated', False)
            and user.__class__.__name__ == 'Staff'
            and getattr(user, 'role', None) in ('owner', 'manager')
        )
