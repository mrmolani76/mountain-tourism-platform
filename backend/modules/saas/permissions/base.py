"""DRF permissions for central SaaS APIs."""

from rest_framework.permissions import BasePermission


class IsSaasUser(BasePermission):
    """Request.user must be an authenticated active SaasUser."""

    def has_permission(self, request, view):
        user = getattr(request, 'user', None)
        if user is None or not getattr(user, 'is_authenticated', False):
            return False
        # SaasUser instances only (not tenant Staff).
        return user.__class__.__name__ == 'SaasUser' and getattr(user, 'is_active', False)
