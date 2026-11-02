"""DRF permissions for tenant APIs."""

from rest_framework.permissions import BasePermission


class IsTenantStaff(BasePermission):
    """Authenticated Staff on a resolved tenant host."""

    def has_permission(self, request, view):
        if getattr(request, 'is_central', False):
            return False
        user = getattr(request, 'user', None)
        return bool(
            user
            and getattr(user, 'is_authenticated', False)
            and user.__class__.__name__ == 'Staff'
        )


class IsOwner(BasePermission):
    """Tenant owner role only (PDF §5A: owner runs complex configuration)."""

    def has_permission(self, request, view):
        user = getattr(request, 'user', None)
        return bool(
            user
            and getattr(user, 'is_authenticated', False)
            and getattr(user, 'role', None) == 'owner'
        )


class CanResetStaffPassword(BasePermission):
    def has_permission(self, request, view):
        user = getattr(request, 'user', None)
        return bool(user and getattr(user, 'can_reset_password', False))
