"""Super-admin users on the central DB (no SSO)."""

from django.db import models


class SaasUser(models.Model):
    email = models.EmailField(max_length=254, unique=True)
    password = models.CharField(max_length=128)
    is_active = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        db_table = 'saas_user'
        ordering = ['email']

    def __str__(self):
        return self.email

    @property
    def is_authenticated(self):
        return True

    @property
    def is_anonymous(self):
        return False
