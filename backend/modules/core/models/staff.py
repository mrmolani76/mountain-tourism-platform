"""Staff + outlet access (lives in the tenant DB)."""

from django.db import models


class StaffRole(models.TextChoices):
    """Roles aligned with PDF §3 (tenant staff). Super admin is SaasUser on central."""

    OWNER = 'owner', 'Owner'
    MANAGER = 'manager', 'Manager'
    FRONT_DESK = 'front_desk', 'Front Desk'
    CASHIER = 'cashier', 'Cashier'
    RENTAL_DESK = 'rental_desk', 'Rental Desk'
    SERVICE_STAFF = 'service_staff', 'Service Staff'
    TRANSPORT_DESK = 'transport_desk', 'Transport Desk'
    LIFT_OPERATOR = 'lift_operator', 'Lift Operator'


class Staff(models.Model):
    email = models.EmailField(max_length=254, unique=True)
    password = models.CharField(max_length=128)
    role = models.CharField(
        max_length=32,
        choices=StaffRole.choices,
        default=StaffRole.FRONT_DESK,
    )
    is_active = models.BooleanField(default=True)
    last_login = models.DateTimeField(null=True, blank=True)
    created_at = models.DateTimeField(auto_now_add=True)

    objects = models.Manager()

    class Meta:
        db_table = 'core_staff'
        ordering = ['email']

    def __str__(self):
        return self.email

    @property
    def is_authenticated(self):
        return True

    @property
    def is_anonymous(self):
        return False

    @property
    def can_reset_password(self):
        return self.role in (StaffRole.OWNER, StaffRole.MANAGER)

    @property
    def usable_password(self):
        return self.password


class StaffOutletAccess(models.Model):
    staff = models.ForeignKey(
        Staff, on_delete=models.CASCADE, related_name='outlet_accesses'
    )
    outlet = models.ForeignKey(
        'core.Outlet',
        on_delete=models.CASCADE,
        related_name='staff_accesses',
    )
    created_at = models.DateTimeField(auto_now_add=True)

    objects = models.Manager()

    class Meta:
        db_table = 'core_staff_outlet_access'
        unique_together = [['staff', 'outlet']]
        ordering = ['staff', 'outlet']
