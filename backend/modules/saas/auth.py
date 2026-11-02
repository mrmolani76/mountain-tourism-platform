"""Session auth for SaasUser on the central host."""

from rest_framework.authentication import BaseAuthentication
from rest_framework.exceptions import AuthenticationFailed

from modules.saas.models import SaasUser


class SaasSessionAuthentication(BaseAuthentication):
    def authenticate(self, request):
        session = getattr(request, 'session', None)
        if not session:
            return None
        saas_user_id = session.get('saas_user_id')
        if not saas_user_id:
            return None
        try:
            user = SaasUser.objects.get(pk=saas_user_id)
        except SaasUser.DoesNotExist:
            raise AuthenticationFailed('Invalid SaaS session.')
        if not user.is_active:
            raise AuthenticationFailed('SaaS user inactive.')
        return (user, None)
